<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'user_id','owner_id','item_id','variant_id',
        'quantity','price','offer_price','item_name','variant_label',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'offer_price' => 'decimal:2',
        'quantity'    => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function variant()
    {
        return $this->belongsTo(ItemVariant::class, 'variant_id');
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
