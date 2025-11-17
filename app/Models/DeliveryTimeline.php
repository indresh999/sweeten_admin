<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryTimeline extends Model
{
    protected $table = 'delivery_timeline';

    protected $fillable = ['order_id', 'status', 'message', 'meta'];

    public $timestamps = false; // we use created_at manually

    protected $casts = [
        'meta' => 'array'
    ];
}