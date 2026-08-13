<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryBoy extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'delivery_boys';

    protected $guard = 'delivery';

    protected $fillable = [
        'full_name', 'email', 'phone_number', 'password', 'picture',
        'vehicle_type', 'working_city', 'working_city_lat', 'working_city_lng',
        'status', 'latitude', 'longitude',
        'max_active_orders', 'current_active_orders',
        'is_verified', 'last_login_at', 'otp', 'otp_expires_at',
        'fcm_token', 'bank_account_number', 'bank_ifsc',
        'bank_account_name', 'upi_id', 'payment_type',
        'wallet_limit', 'wallet_collected', 'has_pending_submission',
        'onboarding_step',
    ];

    protected $hidden = ['password', 'remember_token', 'otp', 'otp_expires_at'];

    protected $casts = [
        'latitude'              => 'float',
        'longitude'             => 'float',
        'current_active_orders' => 'integer',
        'max_active_orders'     => 'integer',
        'is_verified'           => 'boolean',
        'has_pending_submission'=> 'boolean',
        'wallet_limit'          => 'decimal:2',
        'wallet_collected'      => 'decimal:2',
        'last_login_at'         => 'datetime',
        'otp_expires_at'        => 'datetime',
    ];

    public function documents()
    {
        return $this->hasMany(DeliveryDocument::class, 'delivery_boy_id');
    }

    public function assignments()
    {
        return $this->hasMany(DeliveryAssignment::class, 'delivery_boy_id');
    }

    public function earnings()
    {
        return $this->hasMany(DeliveryEarning::class, 'delivery_boy_id');
    }

    public function cashSubmissions()
    {
        return $this->hasMany(DeliveryCashSubmission::class, 'delivery_boy_id');
    }
}
