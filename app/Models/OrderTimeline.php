<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTimeline extends Model
{
    protected $table = 'order_timelines';
    public $timestamps = false;
    protected $fillable = ['order_id', 'status', 'message', 'created_at'];
    protected $casts = ['created_at' => 'datetime'];

    public function order() { return $this->belongsTo(Order::class); }
}
