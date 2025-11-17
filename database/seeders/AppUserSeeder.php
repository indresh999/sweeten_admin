<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AppUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['full_name'=>'Rohit Patel','email'=>'rohit@betul.test','phone_number'=>'9000002201','password'=>Hash::make('userpass'),'is_verified'=>1],
            ['full_name'=>'Meena Solanki','email'=>'meena@betul.test','phone_number'=>'9000002202','password'=>Hash::make('userpass'),'is_verified'=>1],
            ['full_name'=>'Anita Gupta','email'=>'anita@betul.test','phone_number'=>'9000002203','password'=>Hash::make('userpass'),'is_verified'=>1],
            ['full_name'=>'Sunil Verma','email'=>'sunil@betul.test','phone_number'=>'9000002204','password'=>Hash::make('userpass'),'is_verified'=>1],
            ['full_name'=>'Pooja Sharma','email'=>'pooja@betul.test','phone_number'=>'9000002205','password'=>Hash::make('userpass'),'is_verified'=>1],
        ];

        DB::table('app_users')->insert($users);
    }
}