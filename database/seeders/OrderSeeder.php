<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // get some users and shops
        $users = DB::table('app_users')->pluck('id')->toArray();
        $shops = DB::table('app_owner_shops')->pluck('shop_id')->toArray();
        $addresses = DB::table('user_addresses')->get();

        $orders = [];
        for ($i=1;$i<=20;$i++) {
            $userId = $users[array_rand($users)];
            $shopId = $shops[array_rand($shops)];
            // pick address from that user if exists
            $addr = $addresses->where('user_id',$userId)->first();
            if (!$addr) {
                $addr = $addresses->first();
            }
            $total = rand(100,1200);
            $gst = 5;
            $tax = intval($total * $gst / 100);
            $delivery = rand(20,60);
            $handling = rand(5,20);
            $packing = rand(5,15);
            $final = $total + $tax + $delivery + $handling + $packing;

            $orders[] = [
                'user_id' => $userId,
                'shop_id' => $shopId,
                'total_amount' => $total,
                'status' => 'pending',
                'gst_percent' => $gst,
                'tax_amount' => $tax,
                'delivery_charge' => $delivery,
                'handling_fee' => $handling,
                'packing_fee' => $packing,
                'final_amount' => $final,
                'address_label' => $addr->label,
                'address_line' => $addr->address_line,
                'city' => $addr->city,
                'state' => $addr->state,
                'pincode' => $addr->pincode,
                'lat' => $addr->lat,
                'lng' => $addr->lng,
                'created_at' => Carbon::now()->subDays(rand(0,5)),
                'updated_at' => Carbon::now(),
            ];
        }

        DB::table('orders')->insert($orders);
    }
}