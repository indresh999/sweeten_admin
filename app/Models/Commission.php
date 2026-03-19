<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = [
        'type',
        'category_id',
        'subcategory_id',
        'vendor_id',
        'commission_type',
        'commission_value',
        'min_amount',
        'max_amount',
        'status',
        'priority'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
}