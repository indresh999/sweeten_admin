<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class DeliveryBoy extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'delivery_boys';

    protected $fillable = [
        'full_name',
        'phone_number',
        'password',
        'picture',
        'vehicle_type',
        'status',
        'latitude',
        'longitude',
        'max_active_orders',
        'current_active_orders',
        'is_verified',
        'last_login_at',
    ];

    protected $hidden = [ 'password', 'remember_token' ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'current_active_orders' => 'integer',
        'max_active_orders' => 'integer',
        'is_verified' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function documents()
    {
        return $this->hasMany(DeliveryDocument::class, 'delivery_boy_id');
    }

    public function assignments()
    {
        return $this->hasMany(DeliveryAssignment::class, 'delivery_boy_id');
    }

    public function orders()
    {
        return $this->hasManyThrough(
            Order::class,
            DeliveryAssignment::class,
            'delivery_boy_id',   // FK in DeliveryAssignment
            'id',                // PK in Order
            'id',                // PK in DeliveryBoy
            'order_id'           // FK in DeliveryAssignment
        );
    }
}