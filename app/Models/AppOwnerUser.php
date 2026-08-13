<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AppOwnerUser extends Model
{
    protected $table      = 'app_owner_shops';
    protected $primaryKey = 'shop_id';
    public    $incrementing = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'full_name', 'email', 'password', 'phone_number', 'api_token', 'fcm_token',
        'restaurant_name', 'restaurant_address', 'city', 'state',
        'zip_code', 'country', 'latitude', 'longitude',
        'gst_number', 'pan_number', 'status',
        'is_featured', 'featured_sort_order',
        'is_popular', 'popular_sort_order', 'popular_area',
        'fssai_number', 'bank_account_name', 'bank_account_number',
        'bank_ifsc', 'bank_name', 'payment_type', 'upi_id',
        'last_active_at',
        'otp_code', 'otp_expires_at', 'onboarding_step',
    ];

    protected $hidden = ['password', 'otp_code', 'otp_expires_at', 'api_token', 'fcm_token'];

    protected $casts = [
        'latitude'             => 'float',
        'longitude'            => 'float',
        'onboarding_step'      => 'integer',
        'otp_expires_at'       => 'datetime',
        'is_featured'          => 'boolean',
        'featured_sort_order'  => 'integer',
        'is_popular'           => 'boolean',
        'popular_sort_order'   => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────
    public function images()
    {
        return $this->hasMany(ShopImage::class, 'shop_id', 'shop_id');
    }

    public function documents()
    {
        return $this->hasMany(\App\Models\ShopDocument::class, 'shop_id', 'shop_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'shop_id', 'shop_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'shop_id', 'shop_id');
    }

    public function reviews()
    {
        return $this->hasMany(ShopReview::class, 'shop_id', 'shop_id');
    }

    public function schedules()
    {
        return $this->hasMany(ShopSchedule::class, 'shop_id', 'shop_id');
    }

    // ── Helpers ────────────────────────────────────────────
    public function isOpenNow(): bool
    {
        $schedule = $this->schedules()
            ->where('day_of_week', now()->dayOfWeek)
            ->first();

        if (!$schedule || $schedule->is_closed) return false;

        $now = now()->format('H:i:s');
        return $now >= $schedule->open_time && $now <= $schedule->close_time;
    }

    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(30));
        $this->update(['api_token' => $token]);
        return $token;
    }
}
