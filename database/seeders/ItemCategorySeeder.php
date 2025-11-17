<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemCategorySeeder extends Seeder
{
    public function run(): void
    {
        $cats = [
            ['category_name'=>'Sweets','status'=>'active'],
            ['category_name'=>'Namkeen','status'=>'active'],
            ['category_name'=>'Bakery','status'=>'active'],
            ['category_name'=>'Dairy','status'=>'active'],
            ['category_name'=>'Beverages','status'=>'active'],
            ['category_name'=>'Snacks','status'=>'active'],
        ];
        DB::table('item_categories')->insert($cats);
    }
}