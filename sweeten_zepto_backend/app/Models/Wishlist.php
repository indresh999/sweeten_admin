<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $table = 'wishlists';
    public $timestamps = false;

    protected $fillable = ['user_id','item_id'];

    public function item()
    {
        return $this->belongsTo(Item::class)->with(['variants', 'owner:shop_id,restaurant_name']);
    }
}
