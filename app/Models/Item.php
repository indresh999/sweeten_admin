<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Item extends Model
{
    protected $fillable = [
        'shop_id',
        'category_id',
        'subcategory_id',
        'item_name',
        'description',
        'price',
        'offer_price',
        'min_quantity',
        'weight_or_piece',
        'gst_percent',
        'status',
        'images',
        'cgst',
        'sgst'
    ];

    protected $casts = [
        'images' => 'array',
    ];

    // 👇 ADD THIS
    protected $appends = ['image_urls'];

    /**
     * Return full image URLs
     */
   public function getImageUrlsAttribute()
{
    if (empty($this->images)) {
        return [];
    }

    return collect($this->images)->map(function ($path) {
        return asset('storage/' . $path);
        // OR: Storage::disk('public')->url($path);
    })->toArray();
}

    public function category()
    {
        return $this->belongsTo(ItemCategory::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(ItemSubcategory::class);
    }

    public function owner()
    {
        return $this->belongsTo(AppOwnerUser::class, 'shop_id', 'shop_id');
    }
}