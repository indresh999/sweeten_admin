<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliveryRejectReason;

class DeliveryRejectReasonSeeder extends Seeder
{
    public function run(): void
    {
        $reasons = [
            ['reason' => 'Too far from my location', 'sort_order' => 1],
            ['reason' => 'Currently busy with another order', 'sort_order' => 2],
            ['reason' => 'Vehicle breakdown / not available', 'sort_order' => 3],
            ['reason' => 'Traffic conditions are bad', 'sort_order' => 4],
            ['reason' => 'Personal emergency', 'sort_order' => 5],
            ['reason' => 'Shop is too far', 'sort_order' => 6],
            ['reason' => 'Customer location not reachable', 'sort_order' => 7],
            ['reason' => 'Other', 'sort_order' => 8],
        ];

        foreach ($reasons as $r) {
            DeliveryRejectReason::updateOrCreate(
                ['reason' => $r['reason']],
                ['is_active' => true, 'sort_order' => $r['sort_order']]
            );
        }
    }
}
