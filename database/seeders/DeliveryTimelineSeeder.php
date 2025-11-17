<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryTimelineSeeder extends Seeder
{
    public function run(): void
    {
        $orders = DB::table('orders')->limit(6)->pluck('id')->toArray();
        $rows = [];
        foreach ($orders as $id) {
            $rows[] = ['order_id'=>$id,'status'=>'created','message'=>'Order placed','created_at'=>now(),'updated_at'=>now()];
            $rows[] = ['order_id'=>$id,'status'=>'assigned','message'=>'Delivery assigned','created_at'=>now(),'updated_at'=>now()];
        }
        DB::table('delivery_timeline')->insert($rows);
    }
}