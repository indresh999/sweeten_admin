<?php

use App\Models\DeliveryTimeline;

if (!function_exists('addOrderTimeline')) {

    function addOrderTimeline(
        int $orderId,
        string $status,
        string $message = null,
        array $meta = []
    ) {
        DeliveryTimeline::create([
            'order_id'   => $orderId,
            'status'     => $status,
            'message'    => $message,
            'meta'       => $meta,
            'created_at' => now(),
        ]);
    }
}