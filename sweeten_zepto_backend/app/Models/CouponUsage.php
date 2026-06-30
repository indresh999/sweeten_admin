<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    protected $table = 'coupon_usages';
    public $timestamps = false;

    protected $fillable = ['coupon_id','user_id','order_id','discount_given','used_at'];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
