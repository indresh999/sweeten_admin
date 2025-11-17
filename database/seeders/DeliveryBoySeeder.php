<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryBoySeeder extends Seeder
{
    public function run(): void
    {
        $boys = [
            ['Mukesh Verma','9000003301',21.9031,77.8990],
            ['Lokesh Sharma','9000003302',21.9010,77.9020],
            ['Rahul Sen','9000003303',21.9051,77.9010],
            ['Suresh Kumar','9000003304',21.9005,77.9040],
            ['Ramesh Patil','9000003305',21.9020,77.9055],
            ['Amit Joshi','9000003306',21.8992,77.9028],
            ['Vikas Jain','9000003307',21.9070,77.9000],
            ['Pavan Singh','9000003308',21.8950,77.9060],
            ['Jayesh Mishra','9000003309',21.9065,77.9072],
            ['Nitin Gupta','9000003310',21.8980,77.8980],
        ];

        $rows = [];
        foreach ($boys as $b) {
            [$name,$phone,$lat,$lng] = $b;
            $rows[] = [
                'full_name' => $name,
                'phone_number' => $phone,
                'status' => 'online',
                'latitude' => $lat,
                'longitude' => $lng,
                'max_active_orders' => rand(1,4),
                'current_active_orders' => 0,
            ];
        }

        DB::table('delivery_boys')->insert($rows);
    }
}