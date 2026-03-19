<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    protected $fillable = [
        'type',
        'item_id',
        'category_id',
        'subcategory_id',
        'commission_percent',
        'min_amount',
        'max_amount',
        'priority',
        'status'
    ];
}