<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Policy extends Model
{
    protected $fillable = ['title', 'slug', 'content', 'is_active', 'sort_order'];

    protected static function booted(): void
    {
        static::creating(function (Policy $policy) {
            if (empty($policy->slug)) {
                $policy->slug = Str::slug($policy->title);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
