<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopImage extends Model
{
    protected $fillable = ['shop_id', 'tag', 'image_path'];

    public function shop()
    {
        return $this->belongsTo(AppOwnerUser::class, 'shop_id');
    }
   
}
