<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformFee extends Model
{
    protected $table = 'platform_fees';

    protected $fillable = [
        'handling_fee',
        'packing_fee',
        'min_order_amount',
        'max_order_amount',
        'status',
        'priority'
    ];

    protected $casts = [
        'handling_fee' => 'float',
        'packing_fee' => 'float',
        'min_order_amount' => 'float',
        'max_order_amount' => 'float',
        'status' => 'boolean',
    ];

    //  Helper: get fee based on order amount
    public static function getFee($orderAmount)
    {
        $fee = self::where('status', 1)
            ->where(function ($q) use ($orderAmount) {
                $q->where('min_order_amount', '<=', $orderAmount)
                  ->orWhereNull('min_order_amount');
            })
            ->where(function ($q) use ($orderAmount) {
                $q->where('max_order_amount', '>=', $orderAmount)
                  ->orWhereNull('max_order_amount');
            })
            ->orderBy('priority')
            ->first();

        if (!$fee) return 0;

        return $fee->handling_fee + $fee->packing_fee;
    }
}