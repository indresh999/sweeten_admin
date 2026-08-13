<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemMedia extends Model
{
    protected $table = 'item_media';

    protected $fillable = [
        'item_id', 'file_type', 'original_path', 'processed_path', 'thumb_path',
        'mime_type', 'size_bytes', 'sort_order', 'is_thumbnail',
        'processing_status', 'processing_error',
    ];

    protected $casts = [
        'is_thumbnail' => 'boolean',
        'sort_order'   => 'integer',
        'size_bytes'   => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function getUrlAttribute(): ?string
    {
        $path = $this->processed_path ?? $this->original_path;
        if (!$path) return null;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
        return asset('storage/' . $path);
    }

    public function getThumbUrlAttribute(): ?string
    {
        if ($this->thumb_path) {
            if (str_starts_with($this->thumb_path, 'http://') || str_starts_with($this->thumb_path, 'https://')) return $this->thumb_path;
            return asset('storage/' . $this->thumb_path);
        }
        return $this->url;
    }

    public function isPending(): bool
    {
        return $this->processing_status === 'pending';
    }

    public function isDone(): bool
    {
        return $this->processing_status === 'done';
    }
}
