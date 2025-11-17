<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ShopSeeder::class,
            AppUserSeeder::class,     // 👈 MUST run before addresses
            UserAddressSeeder::class,
            ItemCategorySeeder::class,
            ItemSeeder::class,
            DeliveryBoySeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
            CartSeeder::class,
            DeliveryAssignmentSeeder::class,
            DeliveryTimelineSeeder::class,
        ]);
    }
}