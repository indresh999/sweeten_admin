<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create a DB notification record and optionally push FCM.
     */
    public static function send(
        int $userId,
        string $title,
        string $body,
        string $type = 'general',
        string $refType = null,
        int $refId = null,
        string $userType = 'app_user'
    ): void {
        try {
            Notification::create([
                'user_id'        => $userId,
                'user_type'      => $userType,
                'title'          => $title,
                'body'           => $body,
                'type'           => $type,
                'reference_type' => $refType,
                'reference_id'   => $refId,
                'is_read'        => false,
                'sent_at'        => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('[NotificationService] Failed to store notification: ' . $e->getMessage());
        }
    }

    public static function orderPlaced(int $userId, int $orderId): void
    {
        self::send($userId, 'Order Placed! 🎉', "Your order #$orderId has been placed successfully.", 'order', 'order', $orderId);
    }

    public static function orderConfirmed(int $userId, int $orderId): void
    {
        self::send($userId, 'Order Confirmed ✅', "Great news! Your order #$orderId is confirmed and being prepared.", 'order', 'order', $orderId);
    }

    public static function orderOutForDelivery(int $userId, int $orderId): void
    {
        self::send($userId, 'Out for Delivery 🛵', "Your order #$orderId is on its way!", 'order', 'order', $orderId);
    }

    public static function orderDelivered(int $userId, int $orderId): void
    {
        self::send($userId, 'Order Delivered 🎊', "Your order #$orderId has been delivered. Enjoy!", 'order', 'order', $orderId);
    }

    public static function orderCancelled(int $userId, int $orderId): void
    {
        self::send($userId, 'Order Cancelled', "Your order #$orderId has been cancelled. Refund (if any) will be processed.", 'order', 'order', $orderId);
    }
}
