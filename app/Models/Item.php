<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Item extends Model
{
    protected $appends = ['image_urls', 'default_variant'];

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
    
    public function variants()
    {
        return $this->hasMany(ItemVariant::class);
    }

    // OPTIONAL: default variant
    public function defaultVariant()
    {
        return $this->hasOne(ItemVariant::class)->where('is_default', true);
    }

    public function getDefaultVariantAttribute()
    {
        return $this->variants()
            ->where('is_default', true)
            ->where('status', 'active')
            ->first();
    }
}