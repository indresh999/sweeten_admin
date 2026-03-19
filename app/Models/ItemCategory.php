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
        'is_featured',
        'hsn',
        'tax',
        'commission_percent',
        'commission_type'
    ];

    public function items()
    {
        return $this->hasMany(Item::class, 'category_id');
    }

    public function getImageAttribute($value)
    {
        return $value ? asset($value) : null;
    }

     public function parent()
    {
        return $this->belongsTo(ItemSubcategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ItemSubcategory::class, 'parent_id');
    }
}