<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ItemOffer extends Model
{
    protected $fillable = [
        'shop_id', 'title', 'description',
        'item_id', 'category_id',
        'offer_type', 'discount_value', 'max_discount', 'min_order_value',
        'badge_label', 'valid_from', 'valid_until', 'status', 'sort_order',
    ];

    protected $casts = [
        'discount_value'  => 'float',
        'max_discount'    => 'float',
        'min_order_value' => 'float',
        'status'          => 'boolean',
        'valid_from'      => 'datetime',
        'valid_until'     => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────
    public function shop()
    {
        return $this->belongsTo(AppOwnerUser::class, 'shop_id', 'shop_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id')
            ->select(['id', 'item_name', 'price', 'offer_price', 'images']);
    }

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id')
            ->select(['id', 'category_name']);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', true)
            ->where(fn($q) => $q->whereNull('valid_from')->orWhere('valid_from', '<=', now()))
            ->where(fn($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()));
    }

    // ── Helpers ────────────────────────────────────────────────────────────────
    public function isCurrentlyActive(): bool
    {
        if (!$this->status) return false;
        $now = now();
        if ($this->valid_from && $now->lt($this->valid_from)) return false;
        if ($this->valid_until && $now->gt($this->valid_until)) return false;
        return true;
    }

    public function calculateDiscount(float $price): float
    {
        if ($this->offer_type === 'flat') {
            return min($price, (float) $this->discount_value);
        }
        if ($this->offer_type === 'percent') {
            $discount = $price * ($this->discount_value / 100);
            return $this->max_discount ? min($discount, (float) $this->max_discount) : $discount;
        }
        return 0;
    }
}
