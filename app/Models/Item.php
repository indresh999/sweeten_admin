<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Item extends Model
{
    protected $appends = ['image_urls', 'default_variant'];

   protected $fillable = [
    // Relations
    'shop_id',
    'category_id',
    'subcategory_id',

    // Core
    'item_name',
    'slug',
    'description',
    'item_type',
    'status',

    // Pricing & Tax
    'price',
    'offer_price',
    'gst_percent',
    'cgst',
    'sgst',
    'igst',
    'hsn_code',
    'cess_percent',
    'is_tax_inclusive',

    // Inventory
    'sku',
    'stock_quantity',
    'track_inventory',
    'low_stock_alert',
    'min_quantity',
    'max_quantity',

    // Food specific
    'is_veg',
    'is_jain',
    'spice_level',
    'preparation_time',

    // Media
    'images',
    'thumbnail_image',
    'video_url',

    // Display
    'is_featured',
    'display_order',
    'badge',

    // Analytics
    'rating_avg',
    'rating_count',
    'total_sold',

    // Misc
    'weight_or_piece',
    'allow_custom_notes',
    'commission_percent',
    'commission_type'
];

    protected $casts = [
        'images' => 'array',
    ];


    protected $hidden = ['images'];
    
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