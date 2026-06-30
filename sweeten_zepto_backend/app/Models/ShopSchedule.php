<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSchedule extends Model
{
    protected $table = 'shop_schedules';
    public $timestamps = false;

    protected $fillable = ['shop_id','day_of_week','open_time','close_time','is_closed'];

    protected $casts = ['is_closed' => 'boolean'];
}
