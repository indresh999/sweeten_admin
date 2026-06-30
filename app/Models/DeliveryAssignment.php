<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryAssignment extends Model
{
    protected $table    = 'delivery_assignments';
    protected $fillable = ['order_id','delivery_boy_id','status','expected_delivery','accepted_at','picked_at','delivered_at','rejected_at'];
    protected $casts    = ['expected_delivery'=>'datetime','accepted_at'=>'datetime','picked_at'=>'datetime','delivered_at'=>'datetime','rejected_at'=>'datetime'];

    public function order()      { return $this->belongsTo(Order::class); }
    public function boy()        { return $this->belongsTo(DeliveryBoy::class,'delivery_boy_id'); }
}
