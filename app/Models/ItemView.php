<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemView extends Model
{
    public $timestamps  = false;
    protected $table    = 'item_views';

    protected $fillable = ['item_id', 'shop_id', 'user_id', 'city', 'state', 'lat', 'lng', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function item() { return $this->belongsTo(Item::class); }
    public function shop() { return $this->belongsTo(AppOwnerUser::class, 'shop_id', 'shop_id'); }
}
