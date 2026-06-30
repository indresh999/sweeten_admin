<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id','shop_id',
        'total_amount','gst_percent','tax_amount','delivery_charge',
        'handling_fee','packing_fee','discount_amount','wallet_used','final_amount',
        'coupon_id','coupon_code',
        'status','cancel_reason_id','cancel_remark',
        'payment_method','payment_status','payment_reference',
        'special_instructions',
        'address_label','address_line','city','state','pincode','lat','lng',
        'shop_rating','delivery_rating','rated_at',
        'expected_delivery_at',
    ];

    protected $casts = [
        'total_amount'    => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'handling_fee'    => 'decimal:2',
        'packing_fee'     => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'wallet_used'     => 'decimal:2',
        'final_amount'    => 'decimal:2',
        'lat'             => 'float',
        'lng'             => 'float',
        'rated_at'        => 'datetime',
        'expected_delivery_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function owner()
    {
        return $this->belongsTo(AppOwnerUser::class, 'shop_id', 'shop_id');
    }

    public function user()
    {
        return $this->belongsTo(AppUser::class, 'user_id');
    }

    public function cancelReason()
    {
        return $this->belongsTo(CancelReason::class, 'cancel_reason_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function assignment()
    {
        return $this->hasOne(DeliveryAssignment::class, 'order_id');
    }

    public function timeline()
    {
        return $this->hasMany(DeliveryTimeline::class, 'order_id')->orderBy('created_at');
    }

    public function earning()
    {
        return $this->hasOne(DeliveryEarning::class, 'order_id');
    }
}
