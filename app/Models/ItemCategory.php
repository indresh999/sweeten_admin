<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    protected $table = 'item_categories';

    protected $fillable = [
        'category_name',
        'description',
        'status',
        'image',
        'category_type',
        'is_featured'
    ];

    public function items()
    {
        return $this->hasMany(Item::class, 'category_id');
    }

    public function getImageAttribute($value)
    {
        return $value ? asset($value) : null;
    }
}