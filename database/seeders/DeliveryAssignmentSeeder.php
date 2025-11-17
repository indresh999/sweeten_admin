<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        // assign first few orders
        $orders = DB::table('orders')->limit(6)->pluck('id')->toArray();
        $boys = DB::table('delivery_boys')->pluck('id')->toArray();

        $rows = [];
        foreach ($orders as $i => $orderId) {
            $rows[] = [
                'order_id' => $orderId,
                'delivery_boy_id' => $boys[$i % count($boys)],
                'status' => 'assigned',
                'expected_delivery' => now()->addMinutes(30 + ($i*5)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('delivery_assignments')->insert($rows);

        // bump current_active_orders for those boys
        foreach ($rows as $r) {
            DB::table('delivery_boys')->where('id',$r['delivery_boy_id'])->increment('current_active_orders');
        }
    }
}