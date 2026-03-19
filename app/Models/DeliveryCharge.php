<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryCharge extends Model
{
    protected $table = 'delivery_charges';

    protected $fillable = [
        'min_distance',
        'max_distance',
        'charge_amount',
        'status',
        'priority',
        'free_above_amount'
    ];
}