<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Item extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'shop_id', 'category_id', 'subcategory_id', 'unit_id',
        'item_name', 'slug', 'description', 'item_type',
        'price', 'offer_price',
        'min_quantity', 'max_quantity', 'weight_or_piece', 'allow_custom_notes',
        'is_veg', 'is_jain', 'contains_egg', 'spice_level', 'preparation_time',
        'is_featured', 'display_order', 'badge', 'status',
        'images', 'thumbnail_image', 'video_url',
        'gst_percent', 'cgst', 'sgst', 'igst', 'hsn_code', 'cess_percent', 'is_tax_inclusive',
        'sku', 'stock_quantity', 'track_inventory', 'low_stock_alert',
        'commission_percent', 'commission_type',
        'rating_avg', 'rating_count', 'total_sold',
    ];

    protected $casts = [
        'images'           => 'array',
        'is_veg'           => 'boolean',
        'is_jain'          => 'boolean',
        'contains_egg'     => 'boolean',
        'is_featured'      => 'boolean',
        'allow_custom_notes' => 'boolean',
        'is_tax_inclusive' => 'boolean',
        'track_inventory'  => 'boolean',
        'price'            => 'float',
        'offer_price'      => 'float',
        'gst_percent'      => 'float',
        'commission_percent' => 'float',
        'rating_avg'       => 'float',
        'low_stock_alert'  => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $item) {
            if (empty($item->slug)) {
                $item->slug = Str::slug($item->item_name) . '-' . Str::random(5);
            }
        });
    }

    // ── Relationships ──────────────────────────────────────────────────────────
    public function variants()
    {
        return $this->hasMany(ItemVariant::class)->where('status', 'active');
    }

    public function allVariants()
    {
        return $this->hasMany(ItemVariant::class);
    }

    public function defaultVariant()
    {
        return $this->hasOne(ItemVariant::class)->where('is_default', true)->where('status', 'active');
    }

    public function media()
    {
        return $this->hasMany(ItemMedia::class)->orderBy('sort_order');
    }

    public function readyMedia()
    {
        return $this->hasMany(ItemMedia::class)
            ->where('processing_status', 'done')
            ->orderBy('sort_order');
    }

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(ItemSubcategory::class, 'subcategory_id');
    }

    public function owner()
    {
        return $this->belongsTo(AppOwnerUser::class, 'shop_id', 'shop_id');
    }

    // Combo: this item's component items
    public function comboComponents()
    {
        return $this->hasMany(ItemComboComponent::class, 'combo_id')
            ->with(['item:id,item_name,price,offer_price,images,status', 'variant:id,label,price,offer_price']);
    }

    // Combos that include this item as a component
    public function partOfCombos()
    {
        return $this->hasMany(ItemComboComponent::class, 'item_id');
    }

    public function offers()
    {
        return $this->hasMany(ItemOffer::class, 'item_id')->where('status', true);
    }

    // ── Accessors ──────────────────────────────────────────────────────────────
    public function getImageUrlsAttribute(): array
    {
        if ($this->relationLoaded('readyMedia') && $this->readyMedia->isNotEmpty()) {
            return $this->readyMedia->map(fn($m) => $m->url)->toArray();
        }
        return collect($this->images ?? [])->map(fn($p) =>
            (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) ? $p : asset('storage/' . $p)
        )->toArray();
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail_image) {
            if (str_starts_with($this->thumbnail_image, 'http://') || str_starts_with($this->thumbnail_image, 'https://')) return $this->thumbnail_image;
            return asset('storage/' . $this->thumbnail_image);
        }
        if ($this->relationLoaded('readyMedia')) {
            $thumb = $this->readyMedia->firstWhere('is_thumbnail', true)
                ?? $this->readyMedia->first();
            return $thumb?->thumb_url;
        }
        return null;
    }

    // ── Helpers ────────────────────────────────────────────────────────────────
    public function isCombo(): bool
    {
        return $this->item_type === 'combo';
    }

    public function effectivePrice(): float
    {
        return (float) ($this->offer_price ?? $this->price ?? 0);
    }
}
