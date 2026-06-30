<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopImage extends Model
{
    protected $table = 'shop_images';

    protected $fillable = [
        'shop_id', 'tag', 'image_path',
        'original_path', 'thumb_path',
        'sort_order', 'processing_status', 'processing_error',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function shop()
    {
        return $this->belongsTo(AppOwnerUser::class, 'shop_id', 'shop_id');
    }

    public function getUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function getThumbUrlAttribute(): ?string
    {
        return $this->thumb_path
            ? asset('storage/' . $this->thumb_path)
            : $this->url;
    }

    public function isReady(): bool
    {
        return $this->processing_status === 'done';
    }
}
