<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AppUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'app_users';

    protected $fillable = [
        'full_name','email','phone_number','password',
        'otp','is_verified','otp_code','otp_expires_at','api_token',
        'referral_code','referred_by','dob','gender','picture',
        'wallet_balance','is_blocked','fcm_token','last_active_at',
    ];

    protected $hidden = [
        'password','otp','otp_code','api_token','created_at','updated_at',
    ];

    protected $casts = [
        'is_verified'    => 'boolean',
        'is_blocked'     => 'boolean',
        'wallet_balance' => 'decimal:2',
        'otp_expires_at' => 'datetime',
        'last_active_at' => 'datetime',
    ];

    public function addresses()
    {
        return $this->hasMany(UserAddress::class, 'user_id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class, 'user_id');
    }

    public function getOrCreateWallet(): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $this->id],
            ['balance' => 0]
        );
    }
}
