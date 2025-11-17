<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserAddressSeeder extends Seeder
{
    public function run(): void
    {
        $addresses = [
            ['user_id'=>1,'label'=>'Home','address_line'=>'Shivaji Ward, Betul','city'=>'Betul','state'=>'MP','pincode'=>'460001','lat'=>21.9039,'lng'=>77.8965,'is_default'=>1],
            ['user_id'=>1,'label'=>'Office','address_line'=>'Bus Stand Road','city'=>'Betul','state'=>'MP','pincode'=>'460001','lat'=>21.9042,'lng'=>77.8942,'is_default'=>0],
            ['user_id'=>2,'label'=>'Home','address_line'=>'Amla Road','city'=>'Betul','state'=>'MP','pincode'=>'460001','lat'=>21.9240,'lng'=>77.7580,'is_default'=>1],
            ['user_id'=>3,'label'=>'Home','address_line'=>'Collectorate Road','city'=>'Betul','state'=>'MP','pincode'=>'460001','lat'=>21.8985,'lng'=>77.9030,'is_default'=>1],
            ['user_id'=>4,'label'=>'Home','address_line'=>'Kothi Bazar','city'=>'Betul','state'=>'MP','pincode'=>'460001','lat'=>21.9028,'lng'=>77.9060,'is_default'=>1],
        ];

        DB::table('user_addresses')->insert($addresses);
    }
}