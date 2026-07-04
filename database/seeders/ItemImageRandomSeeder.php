<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\ItemMedia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ItemImageRandomSeeder extends Seeder
{
    public function run(): void
    {
        $srcDir = base_path('items-imagse');

        if (!File::isDirectory($srcDir)) {
            $this->command->error('Directory not found: ' . $srcDir);
            return;
        }

        $files = collect(File::files($srcDir))
            ->filter(fn($f) => in_array(strtolower($f->getExtension()), ['jpg', 'jpeg', 'png', 'webp', 'avif']))
            ->values();

        if ($files->isEmpty()) {
            $this->command->error('No images found in ' . $srcDir);
            return;
        }

        $items = Item::all();

        if ($items->isEmpty()) {
            $this->command->error('No items found in database.');
            return;
        }

        // Clear existing item media
        ItemMedia::truncate();

        foreach ($items as $item) {
            // Create 2-4 random images per item
            $count = rand(2, min(4, $files->count()));
            $selected = $files->random($count);

            // Ensure destination directory exists
            $destDir = storage_path("app/public/item_media/{$item->id}");
            File::ensureDirectoryExists($destDir);

            $order = 0;
            foreach ($selected as $file) {
                $filename = $file->getFilename();
                $relativeDest = "item_media/{$item->id}/{$filename}";

                // Copy file to storage
                File::copy($file->getPathname(), $destDir . '/' . $filename);

                ItemMedia::create([
                    'item_id'            => $item->id,
                    'file_type'          => 'image',
                    'original_path'      => $relativeDest,
                    'processed_path'     => $relativeDest,
                    'thumb_path'         => $relativeDest,
                    'mime_type'          => mime_content_type($file->getPathname()),
                    'size_bytes'         => $file->getSize(),
                    'sort_order'         => $order,
                    'is_thumbnail'       => $order === 0,
                    'processing_status'  => 'done',
                ]);

                $order++;
            }

            // Also update legacy `images` JSON column for admin panel
            $paths = $selected->map(fn($f) => "item_media/{$item->id}/{$f->getFilename()}")->values()->toArray();
            $item->update(['images' => $paths]);

            $this->command->info("Item #{$item->id} ({$item->item_name}) -> {$count} images");
        }

        $this->command->info("Done! Assigned images to {$items->count()} items.");
    }
}
