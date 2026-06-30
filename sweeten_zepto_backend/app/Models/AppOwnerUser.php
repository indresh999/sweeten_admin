<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppOwnerUser extends Model
{
    protected $table      = 'app_owner_shops';
    protected $primaryKey = 'shop_id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'full_name','email','password','phone_number',
        'restaurant_name','restaurant_address','city','state',
        'zip_code','country','latitude','longitude',
        'gst_number','pan_number','status',
    ];

    protected $hidden = ['password','otp_code','otp_expires_at'];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    public function images()
    {
        return $this->hasMany(ShopImage::class, 'shop_id', 'shop_id');
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

    public function isOpenNow(): bool
    {
        $schedule = $this->schedules()
            ->where('day_of_week', now()->dayOfWeek)
            ->first();
        if (!$schedule || $schedule->is_closed) return false;
        $now = now()->format('H:i:s');
        return $now >= $schedule->open_time && $now <= $schedule->close_time;
    }
}
