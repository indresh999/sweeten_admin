<?php

namespace App\Jobs;

use App\Models\ItemMedia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessItemMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(public readonly int $mediaId) {}

    public function handle(): void
    {
        $media = ItemMedia::find($this->mediaId);
        if (!$media || $media->processing_status === 'done') {
            return;
        }

        $media->update(['processing_status' => 'processing']);

        try {
            if ($media->file_type === 'image') {
                $this->processImage($media);
            } else {
                $this->processVideo($media);
            }

            $media->update(['processing_status' => 'done', 'processing_error' => null]);
        } catch (\Throwable $e) {
            Log::error('[ProcessItemMedia] Failed for media #' . $media->id . ': ' . $e->getMessage());
            $media->update([
                'processing_status' => 'failed',
                'processing_error'  => $e->getMessage(),
                // Original file is still available as fallback
                'processed_path'    => $media->processed_path ?? $media->original_path,
            ]);
        }
    }

    private function processImage(ItemMedia $media): void
    {
        $disk         = Storage::disk('public');
        $originalPath = $media->original_path;

        if (!$disk->exists($originalPath)) {
            throw new \RuntimeException("Source file not found: {$originalPath}");
        }

        $baseName = Str::beforeLast(basename($originalPath), '.');
        $ext      = strtolower(pathinfo($originalPath, PATHINFO_EXTENSION));
        $dir      = 'item_media/' . $media->item_id;

        // Full-size: max 1200px wide, 85% quality JPEG
        $fullPath = "{$dir}/{$baseName}_full.jpg";
        $this->resizeAndStore($disk->path($originalPath), $disk->path($fullPath), 1200, null, 85);

        // Thumbnail: 400×400 centre-crop JPEG
        $thumbPath = "{$dir}/{$baseName}_thumb.jpg";
        $this->resizeAndStore($disk->path($originalPath), $disk->path($thumbPath), 400, 400, 80, true);

        $media->update([
            'processed_path' => $fullPath,
            'thumb_path'     => $thumbPath,
        ]);
    }

    private function resizeAndStore(string $src, string $dest, int $width, ?int $height, int $quality, bool $crop = false): void
    {
        if (!is_dir(dirname($dest))) {
            mkdir(dirname($dest), 0755, true);
        }

        // Use Intervention Image if available, otherwise GD fallback
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
        } else {
            // Pure GD fallback
            $this->gdResize($src, $dest, $width, $height, $crop);
        }
    }

    private function gdResize(string $src, string $dest, int $maxW, ?int $maxH, bool $crop): void
    {
        $info = getimagesize($src);
        if (!$info) throw new \RuntimeException("Cannot read image: {$src}");

        [$srcW, $srcH, $type] = [$info[0], $info[1], $info[2]];

        $source = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($src),
            IMAGETYPE_PNG  => imagecreatefrompng($src),
            IMAGETYPE_WEBP => imagecreatefromwebp($src),
            default        => throw new \RuntimeException("Unsupported image type"),
        };

        if ($crop && $maxH) {
            $scale = max($maxW / $srcW, $maxH / $srcH);
            $newW  = (int) round($srcW * $scale);
            $newH  = (int) round($srcH * $scale);
            $canvas = imagecreatetruecolor($maxW, $maxH);
            imagecopyresampled($canvas, $source, ($maxW - $newW) / 2, ($maxH - $newH) / 2, 0, 0, $newW, $newH, $srcW, $srcH);
        } else {
            $scale = min($maxW / $srcW, ($maxH ?? PHP_INT_MAX) / $srcH, 1.0);
            $newW  = (int) round($srcW * $scale);
            $newH  = (int) round($srcH * $scale);
            $canvas = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);
        }

        imagejpeg($canvas, $dest, 85);
        imagedestroy($source);
        imagedestroy($canvas);
    }

    private function processVideo(ItemMedia $media): void
    {
        // Video transcoding requires FFMpeg (optional dependency).
        // For now, mark the original upload as the processed path so it's immediately usable.
        $media->update([
            'processed_path' => $media->original_path,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        ItemMedia::where('id', $this->mediaId)
            ->where('processing_status', 'processing')
            ->update([
                'processing_status' => 'failed',
                'processing_error'  => $e->getMessage(),
            ]);
    }
}
