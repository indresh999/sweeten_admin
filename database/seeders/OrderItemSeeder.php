<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        $orders = DB::table('orders')->pluck('id')->toArray();

        // sample items by shop
        $items = DB::table('items')->select('id','owner_id','price','offer_price')->get();

        $rows = [];
        foreach ($orders as $orderId) {
            // pick 1-4 random items
            $cnt = rand(1,4);
            $picked = $items->random($cnt);
            foreach ($picked as $it) {
                $price = $it->offer_price ? $it->offer_price : $it->price;
                $qty = rand(1,3);
                $rows[] = [
                    'order_id' => $orderId,
                    'item_id' => $it->id,
                    'quantity' => $qty,
                    'price' => $it->price,
                    'offer_price' => $it->offer_price,
                ];
            }
        }

        // Insert
        $chunks = array_chunk($rows, 200);
        foreach ($chunks as $c) {
            DB::table('order_items')->insert($c);
        }
    }
}