<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopReview extends Model
{
    protected $table = 'shop_reviews';
    public $timestamps = false;

    protected $fillable = ['shop_id','user_id','order_id','rating','comment','is_approved'];
    protected $casts = ['rating' => 'integer', 'is_approved' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(AppUser::class, 'user_id');
    }

    public function shop()
    {
        return $this->belongsTo(AppOwnerUser::class, 'shop_id', 'shop_id');
    }
}
