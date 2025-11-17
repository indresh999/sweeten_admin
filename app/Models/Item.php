<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'owner_id','category_id','item_name','description','price',
        'offer_price','min_quantity','weight_or_piece','status','images','gst_percent'
    ];

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    public function owner()
    {
        return $this->belongsTo(AppOwnerUser::class, 'owner_id', 'shop_id');
    }
}