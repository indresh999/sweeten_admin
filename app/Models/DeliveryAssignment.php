<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryAssignment extends Model
{
    protected $fillable = [
        'order_id','delivery_boy_id','status',
        'expected_delivery','accepted_at','rejected_at',
        'picked_at','delivered_at'
    ];

    protected $dates = [
        'expected_delivery','accepted_at','rejected_at','picked_at','delivered_at'
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function boy() {
        return $this->belongsTo(DeliveryBoy::class, 'delivery_boy_id');
    }
}