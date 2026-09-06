<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now()->toDateTimeString();

        if (DB::table('app_owner_shops')->where('email', 'sweetbites_betul@test.com')->exists()) {
            return;
        }

        $shopId = DB::table('app_owner_shops')->insertGetId([
            'restaurant_name'          => 'Sweet Bites Betul',
            'full_name'                => 'Rahul Sharma',
            'phone_number'             => '9876543210',
            'email'                    => 'sweetbites_betul@test.com',
            'password'                 => bcrypt('password'),
            'city'                     => 'Betul',
            'state'                    => 'Madhya Pradesh',
            'restaurant_address'       => 'Near Bus Stand, Betul, Madhya Pradesh 460001',
            'latitude'                 => 21.9113,
            'longitude'                => 77.8993,
            'status'                   => 'active',
            'created_at'               => $now,
            'updated_at'               => $now,
        ]);

        $item1Id = DB::table('items')->insertGetId([
            'shop_id'              => $shopId,
            'category_id'          => 3,
            'subcategory_id'       => 107,
            'item_name'            => 'Classic Milk Cake 500g',
            'description'          => 'Traditional slow-cooked milk cake made with fresh milk and sugar. Rich, caramelized flavor that melts in your mouth.',
            'price'                => 350,
            'offer_price'          => 320,
            'min_quantity'         => 1,
            'max_quantity'         => 10,
            'weight_or_piece'      => '500g',
            'status'               => 'active',
            'item_type'            => 'regular',
            'is_veg'               => 1,
            'is_jain'              => 0,
            'contains_egg'         => 0,
            'is_featured'          => 1,
            'preparation_time'     => 10,
            'allow_custom_notes'   => 1,
            'gst_percent'          => 5,
            'cgst'                 => '2.50',
            'sgst'                 => '2.50',
            'igst'                 => '0.00',
            'cess_percent'         => '0.00',
            'is_tax_inclusive'     => 0,
            'stock_quantity'       => 50,
            'track_inventory'      => 1,
            'low_stock_alert'      => 10,
            'rating_avg'           => 4.6,
            'rating_count'         => 85,
            'total_sold'           => 320,
            'display_order'        => 0,
            'images'               => json_encode(['https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=400&h=300&fit=crop']),
            'created_at'           => $now,
            'updated_at'           => $now,
        ]);

        DB::table('item_variants')->insert([
            'item_id'     => $item1Id,
            'label'       => '500g Box',
            'price'       => 350,
            'offer_price' => 320,
            'gst_percent' => 5,
            'is_default'  => 1,
            'status'      => 'active',
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $item2Id = DB::table('items')->insertGetId([
            'shop_id'              => $shopId,
            'category_id'          => 3,
            'subcategory_id'       => 107,
            'item_name'            => 'Premium Kaju Milk Cake 1kg',
            'description'          => 'Luxurious milk cake topped with premium cashew paste and silver leaf. Perfect for festivals and gifting.',
            'price'                => 750,
            'offer_price'          => 699,
            'min_quantity'         => 1,
            'max_quantity'         => 5,
            'weight_or_piece'      => '1kg',
            'status'               => 'active',
            'item_type'            => 'regular',
            'is_veg'               => 1,
            'is_jain'              => 0,
            'contains_egg'         => 0,
            'is_featured'          => 1,
            'preparation_time'     => 15,
            'allow_custom_notes'   => 1,
            'gst_percent'          => 5,
            'cgst'                 => '2.50',
            'sgst'                 => '2.50',
            'igst'                 => '0.00',
            'cess_percent'         => '0.00',
            'is_tax_inclusive'     => 0,
            'stock_quantity'       => 25,
            'track_inventory'      => 1,
            'low_stock_alert'      => 5,
            'rating_avg'           => 4.8,
            'rating_count'         => 42,
            'total_sold'           => 180,
            'display_order'        => 1,
            'images'               => json_encode(['https://images.unsplash.com/photo-1631452180519-c014fe946bc7?w=400&h=300&fit=crop']),
            'created_at'           => $now,
            'updated_at'           => $now,
        ]);

        DB::table('item_variants')->insert([
            'item_id'     => $item2Id,
            'label'       => '1kg Box',
            'price'       => 750,
            'offer_price' => 699,
            'gst_percent' => 5,
            'is_default'  => 1,
            'status'      => 'active',
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        // Note: max_delivery_radius is set via admin panel, not DB
    }

    public function down(): void
    {
        $shopId = DB::table('app_owner_shops')
            ->where('email', 'sweetbites_betul@test.com')
            ->value('shop_id');

        if ($shopId) {
            DB::table('item_variants')
                ->whereIn('item_id', fn($q) => $q->select('id')->from('items')->where('shop_id', $shopId))
                ->delete();
            DB::table('items')->where('shop_id', $shopId)->delete();
            DB::table('app_owner_shops')->where('shop_id', $shopId)->delete();
        }
    }
};
