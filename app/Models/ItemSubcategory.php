<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemSubcategory extends Model
{
    protected $table = 'item_subcategories';

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'status',
        'image'
    ];

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'subcategory_id');
    }

    public function getImageAttribute($value)
    {
        return $value ? asset($value) : null;
    }
}