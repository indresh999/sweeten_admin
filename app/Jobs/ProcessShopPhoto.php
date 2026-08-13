<?php

namespace App\Jobs;

use App\Models\ShopImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessShopPhoto implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 90;

    public function __construct(public readonly int $shopImageId) {}

    public function handle(): void
    {
        $photo = ShopImage::find($this->shopImageId);
        if (!$photo || $photo->processing_status === 'done') {
            return;
        }

        $photo->update(['processing_status' => 'processing']);

        try {
            $disk     = Storage::disk('public');
            $srcPath  = $photo->original_path ?? $photo->image_path;

            if (!$disk->exists($srcPath)) {
                throw new \RuntimeException("Source file not found: {$srcPath}");
            }

            $baseName = Str::beforeLast(basename($srcPath), '.');
            $dir      = 'shop_photos/' . $photo->shop_id;

            // Full-size: max 1400px wide, 85% quality JPEG
            $fullPath = "{$dir}/{$baseName}_full.jpg";
            $this->resizeAndStore($disk->path($srcPath), $disk->path($fullPath), 1400, null, 85);

            // Thumbnail: 600×400 crop (good for shop cards)
            $thumbPath = "{$dir}/{$baseName}_thumb.jpg";
            $this->resizeAndStore($disk->path($srcPath), $disk->path($thumbPath), 600, 400, 80, true);

            $photo->update([
                'image_path'        => $fullPath,   // replace with processed path
                'thumb_path'        => $thumbPath,
                'processing_status' => 'done',
                'processing_error'  => null,
            ]);
        } catch (\Throwable $e) {
            Log::error('[ProcessShopPhoto] Failed for photo #' . $photo->id . ': ' . $e->getMessage());
            $photo->update([
                'processing_status' => 'failed',
                'processing_error'  => $e->getMessage(),
                // original_path still holds the raw file — usable as fallback
            ]);
        }
    }

    private function resizeAndStore(string $src, string $dest, int $width, ?int $height, int $quality, bool $crop = false): void
    {
        if (!is_dir(dirname($dest))) {
            mkdir(dirname($dest), 0755, true);
        }

        if (class_exists(\Intervention\Image\Facades\Image::class)) {
            $img = \Intervention\Image\Facades\Image::make($src);
            if ($crop && $height) {
                $img->fit($width, $height);
            } else {
                // v2: aspectRatio() and upsize() return void — do NOT chain them
                $img->resize($width, $height, function ($c) {
                    $c->aspectRatio();
                    $c->upsize();
                });
            }
            $img->encode('jpg', $quality)->save($dest);
            return;
        }

        // GD fallback — saves as JPEG regardless of extension
        $info = getimagesize($src);
        if (!$info) throw new \RuntimeException("Cannot read image: {$src}");

        [$srcW, $srcH, $type] = [$info[0], $info[1], $info[2]];
        $source = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($src),
            IMAGETYPE_PNG  => imagecreatefrompng($src),
            IMAGETYPE_WEBP => imagecreatefromwebp($src),
            default        => throw new \RuntimeException("Unsupported image type"),
        };

        if ($crop && $height) {
            $scale  = max($width / $srcW, $height / $srcH);
            $newW   = (int) round($srcW * $scale);
            $newH   = (int) round($srcH * $scale);
            $canvas = imagecreatetruecolor($width, $height);
            imagecopyresampled($canvas, $source, (int)(($width - $newW) / 2), (int)(($height - $newH) / 2), 0, 0, $newW, $newH, $srcW, $srcH);
        } else {
            $scale  = min($width / $srcW, ($height ?? PHP_INT_MAX) / $srcH, 1.0);
            $newW   = (int) round($srcW * $scale);
            $newH   = (int) round($srcH * $scale);
            $canvas = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);
        }

        imagejpeg($canvas, $dest, $quality);
        imagedestroy($source);
        imagedestroy($canvas);
    }

    public function failed(\Throwable $e): void
    {
        ShopImage::where('id', $this->shopImageId)
            ->where('processing_status', 'processing')
            ->update(['processing_status' => 'failed', 'processing_error' => $e->getMessage()]);
    }
}
