<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
protected $fillable = [
    'user_id',
    'shop_id',
    'total_amount',
    'gst_percent',
    'tax_amount',
    'delivery_charge',
    'handling_fee',
    'packing_fee',
    'final_amount',
    'status',
    'cancel_reason_id',
    'cancel_remark',
    'address_label',
    'address_line',
    'city',
    'state',
    'pincode',
    'lat',
    'lng'
];

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function owner()
    {
        return $this->belongsTo(AppOwnerUser::class, 'shop_id');
    }

    public function user()
    {
        return $this->belongsTo(AppUser::class, 'user_id');
    }
    public function cancelReason()
    {
        return $this->belongsTo(CancelReason::class, 'cancel_reason_id');
    }

    public function assignment()
    {
        return $this->hasOne(DeliveryAssignment::class, 'order_id');
    }

    public function deliveryBoy()
    {
        return $this->hasOneThrough(
            DeliveryBoy::class,
            DeliveryAssignment::class,
            'order_id',        // FK on DeliveryAssignment
            'id',              // PK on DeliveryBoy
            'id',              // PK on Order
            'delivery_boy_id'  // FK on DeliveryAssignment
        );
    }
}