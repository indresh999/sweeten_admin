<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopView extends Model
{
    public $timestamps  = false;
    protected $table    = 'shop_views';

    protected $fillable = ['shop_id', 'user_id', 'city', 'state', 'lat', 'lng', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function shop() { return $this->belongsTo(AppOwnerUser::class, 'shop_id', 'shop_id'); }
}
