<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    protected $table = 'banners';

    protected $fillable = [
        'title', 'heading', 'subtitle', 'cta_label',
        'banner_type', 'media_type', 'media_path', 'image_url', 'thumbnail_path',
        'target_type', 'target_id', 'target_url',
        'start_date', 'end_date',
        'sort_order', 'status', 'click_count', 'is_sponsored',
    ];

    protected $casts = [
        'is_sponsored' => 'boolean',
        'start_date'   => 'date',
        'end_date'     => 'date',
    ];

    protected $appends = ['media_url', 'thumbnail_url'];

    // ── Accessors ────────────────────────────────────────────────────────────

    public function getMediaUrlAttribute(): ?string
    {
        if ($this->media_path) {
            return Storage::disk('public')->url($this->media_path);
        }
        return null;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail_path) {
            return Storage::disk('public')->url($this->thumbnail_path);
        }
        return null;
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        $today = now()->toDateString();
        return $query
            ->where('status', 'active')
            ->where(fn($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', $today))
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $today));
    }
}
