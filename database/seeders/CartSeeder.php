<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        // Add a couple of cart items for first 3 users
        $users = DB::table('app_users')->pluck('id')->toArray();
        $items = DB::table('items')->pluck('id')->toArray();

        $rows = [];
        for ($i=0; $i<8; $i++) {
            $rows[] = [
                'user_id' => $users[array_rand($users)],
                'owner_id' => DB::table('items')->where('id',$items[array_rand($items)])->value('owner_id'),
                'item_id' => $items[array_rand($items)],
                'quantity' => rand(1,3),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('cart_items')->insert($rows);
    }
}