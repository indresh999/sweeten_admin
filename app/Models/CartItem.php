<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'user_id', 'owner_id', 'item_id', 'quantity', 'price', 'offer_price'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(AppUser::class, 'user_id');
    }

    public function owner()
    {
        return $this->belongsTo(AppOwnerUser::class, 'owner_id', 'shop_id');
    }
}