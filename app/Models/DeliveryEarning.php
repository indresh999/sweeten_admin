<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryEarning extends Model
{
    protected $table = 'delivery_earnings';
    public $timestamps = false;

    protected $fillable = [
        'delivery_boy_id','order_id','base_earning','bonus','deduction','net_earning','is_paid','paid_at',
    ];

    protected $casts = [
        'base_earning' => 'decimal:2',
        'bonus'        => 'decimal:2',
        'deduction'    => 'decimal:2',
        'net_earning'  => 'decimal:2',
        'is_paid'      => 'boolean',
        'paid_at'      => 'datetime',
    ];

    public function deliveryBoy()
    {
        return $this->belongsTo(DeliveryBoy::class, 'delivery_boy_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
