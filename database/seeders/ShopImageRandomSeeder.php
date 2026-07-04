<?php

namespace Database\Seeders;

use App\Models\AppOwnerUser;
use App\Models\ShopImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ShopImageRandomSeeder extends Seeder
{
    public function run(): void
    {
        $imageDir = storage_path('app/public/shop_images');

        if (!File::isDirectory($imageDir)) {
            $this->command->error('Directory not found: ' . $imageDir);
            return;
        }

        $files = collect(File::files($imageDir))
            ->filter(fn($f) => in_array(strtolower($f->getExtension()), ['jpg', 'jpeg', 'png', 'webp']))
            ->values();

        if ($files->isEmpty()) {
            $this->command->error('No images found in ' . $imageDir);
            return;
        }

        $shops = AppOwnerUser::all();

        if ($shops->isEmpty()) {
            $this->command->error('No shops found in database.');
            return;
        }

        // Delete existing shop images first
        ShopImage::truncate();

        $assigned = 0;

        foreach ($shops as $shop) {
            // Pick a random image for each shop
            $randomFile = $files->random();
            $relativePath = 'shop_images/' . $randomFile->getFilename();

            ShopImage::create([
                'shop_id'           => $shop->shop_id,
                'tag'               => 'cover',
                'image_path'        => $relativePath,
                'original_path'     => $relativePath,
                'thumb_path'        => $relativePath,
                'sort_order'        => 0,
                'processing_status' => 'done',
            ]);

            $this->command->info("Shop #{$shop->shop_id} ({$shop->restaurant_name}) -> {$randomFile->getFilename()}");
            $assigned++;
        }

        $this->command->info("Done! Assigned {$assigned} random images to {$shops->count()} shops.");
    }
}
