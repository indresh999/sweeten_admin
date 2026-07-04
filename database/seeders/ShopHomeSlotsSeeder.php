<?php

namespace Database\Seeders;

use App\Models\AppOwnerUser;
use App\Models\ShopImage;
use Illuminate\Database\Seeder;

class ShopHomeSlotsSeeder extends Seeder
{
    public function run(): void
    {
        // ── Image pool: files that exist in storage/app/public/shop_images ──────
        $imagePool = [
            'shop_images/68e09acbcee95_1759550155.png',
            'shop_images/68e09ad7e0dac_1759550167.png',
            'shop_images/68e1089d68b16_1759578269.jpg',
            'shop_images/68e108dbe33f0_1759578331.jpg',
            'shop_images/68e108e99ff75_1759578345.jpg',
            'shop_images/68e109111ab4f_1759578385.jpg',
            'shop_images/68e10911381a4_1759578385.jpg',
            'shop_images/68e1091140230_1759578385.jpg',
            'shop_images/68e10a8d963c8_1759578765.jpg',
            'shop_images/68e10a8db8d95_1759578765.jpg',
        ];

        // ── Shop metadata overrides ───────────────────────────────────────────
        // [shop_id => [featured_sort, popular_sort, popular_area, image_index]]
        $config = [
            1  => [0, 0, 'Main Market',          0],
            2  => [1, 1, 'Main Market',          1],
            3  => [2, 2, 'Main Market',          2],
            4  => [3, 0, 'Collectorate Road',    3],
            5  => [4, 1, 'Collectorate Road',    4],
            6  => [5, 0, 'Railway Station Area', 5],
            9  => [6, 2, 'Collectorate Road',    6],
            10 => [7, 1, 'Railway Station Area', 7],
            11 => [8, 3, 'Main Market',          8],
            12 => [9, 2, 'Railway Station Area', 9],
        ];

        // Featured: first 6 by featured_sort_order (shops 1-6)
        $featuredIds = [1, 2, 3, 4, 5, 6];

        foreach ($config as $shopId => [$featSort, $popSort, $area, $imgIdx]) {
            $isFeatured = in_array($shopId, $featuredIds);

            // Update home-slot flags
            AppOwnerUser::where('shop_id', $shopId)->update([
                'is_featured'         => $isFeatured,
                'featured_sort_order' => $isFeatured ? $featSort : 0,
                'is_popular'          => true,
                'popular_sort_order'  => $popSort,
                'popular_area'        => $area,
            ]);

            // Assign a cover image if none exists yet
            $hasImage = ShopImage::where('shop_id', $shopId)
                ->whereNotNull('image_path')
                ->where('image_path', '!=', '')
                ->exists();

            if (!$hasImage && isset($imagePool[$imgIdx])) {
                ShopImage::create([
                    'shop_id'            => $shopId,
                    'tag'                => 'cover',
                    'image_path'         => $imagePool[$imgIdx],
                    'sort_order'         => 0,
                    'processing_status'  => 'done',
                ]);
            }
        }

        $this->command->info('✓ ShopHomeSlotsSeeder: featured=' . count($featuredIds) . ' popular=' . count($config));
    }
}
