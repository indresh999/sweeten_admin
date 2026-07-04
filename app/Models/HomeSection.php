<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeSection extends Model
{
    protected $fillable = ['title', 'sort_order'];

    public function categories(): HasMany
    {
        return $this->hasMany(ItemCategory::class, 'home_section_id')
                    ->orderBy('home_sort_order');
    }
}
