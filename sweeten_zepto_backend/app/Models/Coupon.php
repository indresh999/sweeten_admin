<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    protected $fillable = [
        'code','title','description','discount_type','discount_value',
        'min_order_amount','max_discount_amount','usage_limit','usage_per_user',
        'used_count','applicable_to','applicable_ids','valid_from','valid_until',
        'is_active','created_by',
    ];

    protected $casts = [
        'applicable_ids'  => 'array',
        'discount_value'  => 'decimal:2',
        'min_order_amount'=> 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'valid_from'      => 'datetime',
        'valid_until'     => 'datetime',
        'is_active'       => 'boolean',
    ];

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isValid(): bool
    {
        return $this->is_active
            && Carbon::now()->between($this->valid_from, $this->valid_until)
            && ($this->usage_limit === null || $this->used_count < $this->usage_limit);
    }

    public function calculateDiscount(float $orderAmount): float
    {
        if ($orderAmount < $this->min_order_amount) return 0;
        if ($this->discount_type === 'flat') {
            return min((float) $this->discount_value, $orderAmount);
        }
        $discount = $orderAmount * ($this->discount_value / 100);
        if ($this->max_discount_amount) {
            $discount = min($discount, (float) $this->max_discount_amount);
        }
        return round($discount, 2);
    }
}
