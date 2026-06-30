<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryTimeline extends Model
{
    protected $table    = 'delivery_timelines';
    public $timestamps  = false;
    protected $fillable = ['order_id','status','message','created_at'];
    protected $casts    = ['created_at'=>'datetime'];
}
