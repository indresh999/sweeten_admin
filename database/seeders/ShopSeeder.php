<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $shops = [
            [
                'full_name' => 'Betul Sweets Center',
                'email' => 'betul.sweets@example.com',
                'password' => Hash::make('ownerpass'),
                'phone_number' => '9000001101',
                'restaurant_name' => 'Betul Sweets & Namkeen',
                'restaurant_address' => 'Main Road, Betul',
                'city' => 'Betul',
                'state' => 'MP',
                'zip_code' => '460001',
                'country' => 'India',
                'latitude' => 21.9002,
                'longitude' => 77.9047,
                'gst_number' => 'GSTINBETUL001',
                'pan_number' => 'PANBET001',
            ],
            [
                'full_name' => 'Ganj Bakery Betul',
                'email' => 'ganj.bakery@example.com',
                'password' => Hash::make('ownerpass'),
                'phone_number' => '9000001102',
                'restaurant_name' => 'Ganj Bakery',
                'restaurant_address' => 'Ganj Road, Betul',
                'city' => 'Betul',
                'state' => 'MP',
                'zip_code' => '460001',
                'country' => 'India',
                'latitude' => 21.9051,
                'longitude' => 77.9010,
                'gst_number' => 'GSTINBETUL002',
                'pan_number' => 'PANBET002',
            ],
            [
                'full_name' => 'Collectorate Sweets',
                'email' => 'collectorate.sweets@example.com',
                'password' => Hash::make('ownerpass'),
                'phone_number' => '9000001103',
                'restaurant_name' => 'Collectorate Sweets',
                'restaurant_address' => 'Collectorate Road, Betul',
                'city' => 'Betul',
                'state' => 'MP',
                'zip_code' => '460001',
                'country' => 'India',
                'latitude' => 21.8985,
                'longitude' => 77.9030,
                'gst_number' => 'GSTINBETUL003',
                'pan_number' => 'PANBET003',
            ],
            [
                'full_name' => 'Amla Road Bakery',
                'email' => 'amla.bakery@example.com',
                'password' => Hash::make('ownerpass'),
                'phone_number' => '9000001104',
                'restaurant_name' => 'Amla Road Bakery',
                'restaurant_address' => 'Amla Road, Betul',
                'city' => 'Betul',
                'state' => 'MP',
                'zip_code' => '460001',
                'country' => 'India',
                'latitude' => 21.9240,
                'longitude' => 77.7580,
                'gst_number' => 'GSTINBETUL004',
                'pan_number' => 'PANBET004',
            ],
            [
                'full_name' => 'Market Corner Bakes',
                'email' => 'market.bakes@example.com',
                'password' => Hash::make('ownerpass'),
                'phone_number' => '9000001105',
                'restaurant_name' => 'Market Corner Bakes',
                'restaurant_address' => 'Kothi Bazar, Betul',
                'city' => 'Betul',
                'state' => 'MP',
                'zip_code' => '460001',
                'country' => 'India',
                'latitude' => 21.9028,
                'longitude' => 77.9060,
                'gst_number' => 'GSTINBETUL005',
                'pan_number' => 'PANBET005',
            ],
        ];

        DB::table('app_owner_shops')->insert($shops);
    }
}