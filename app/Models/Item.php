<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'name', 'description', 'price', 'category_id', 'subcategory_id', /* etc */
    ];

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
}