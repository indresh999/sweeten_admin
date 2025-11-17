<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        // base items per category (varied)
        $categoryItems = [
            'Sweets' => [
                ['Gulab Jamun', 120], ['Rasgulla', 100], ['Milk Cake', 150], ['Ladoo', 110], ['Kaju Katli', 350]
            ],
            'Namkeen' => [
                ['Mathri', 40], ['Mixture', 60], ['Chivda', 45], ['Kachori', 20], ['Samosa', 15]
            ],
            'Bakery' => [
                ['Black Forest Cake', 500], ['Pineapple Cake', 600], ['Butter Croissant', 40], ['Baguette', 60], ['Bun', 20]
            ],
            'Dairy' => [
                ['Fresh Paneer (250g)', 90], ['Dahi (500g)', 40], ['Milk (1L)', 50]
            ],
            'Beverages' => [
                ['Masala Chai (cup)', 15], ['Cold Coffee', 80], ['Lassi', 50]
            ],
            'Snacks' => [
                ['Veg Sandwich', 70], ['Vada Pav', 30], ['Pav Bhaji', 90], ['French Fries', 80]
            ],
        ];

        // Fetch shop ids
        $shops = DB::table('app_owner_shops')->pluck('shop_id')->toArray();

        // Fetch and normalize category names
        $categoryMapRaw = DB::table('item_categories')->pluck('id', 'category_name')->toArray();

        // Normalize category names (lowercase keys)
        $categoryMap = [];
        foreach ($categoryMapRaw as $name => $id) {
            $categoryMap[strtolower(trim($name))] = $id;
        }

        $itemsToInsert = [];

        foreach ($shops as $shopId) {

            $i = 0;

            // Loop through categories in seeder
            foreach ($categoryItems as $catName => $arr) {

                $key = strtolower($catName); // normalize name

                if (!isset($categoryMap[$key])) {
                    continue; // skip if category missing in DB
                }

                $catId = $categoryMap[$key];

                foreach ($arr as $entry) {

                    [$nameBase, $priceBase] = $entry;

                    $price = intval($priceBase + rand(-10, 50));

                    $itemsToInsert[] = [
                        'owner_id' => $shopId,
                        'category_id' => $catId,
                        'item_name' => $nameBase . " " . ($i + 1),
                        'description' => $nameBase . " from shop #{$shopId}",
                        'price' => $price,
                        'offer_price' => max(1, intval($price * (0.9 - rand(0,5) / 100))),
                        'min_quantity' => 1,
                        'weight_or_piece' => '1 Unit',
                        'status' => 'active',
                        'images' => json_encode([$nameBase . '.jpg']),
                    ];

                    $i++;
                }
            }

            // padding if < 30
            $categoryIds = array_values($categoryMap);

            while ($i < 30 && !empty($categoryIds)) {

                $itemsToInsert[] = [
                    'owner_id' => $shopId,
                    'category_id' => $categoryIds[array_rand($categoryIds)],
                    'item_name' => "Snack " . ($i + 1),
                    'description' => "Snack item " . ($i + 1),
                    'price' => rand(20,150),
                    'offer_price' => rand(10,100),
                    'min_quantity' => 1,
                    'weight_or_piece' => '1 Unit',
                    'status' => 'active',
                    'images' => json_encode(['snack.jpg']),
                ];

                $i++;
            }
        }

        // Insert in chunks
        $chunks = array_chunk($itemsToInsert, 100);
        foreach ($chunks as $c) {
            DB::table('items')->insert($c);
        }
    }
}