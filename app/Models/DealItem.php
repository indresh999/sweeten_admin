<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealItem extends Model
{
    protected $table = 'deal_items';
    public $timestamps = false;

    protected $fillable = ['deal_id','item_id','deal_price','deal_discount_percent','stock_limit','sold_count'];

    protected $casts = [
        'deal_price'             => 'decimal:2',
        'deal_discount_percent'  => 'decimal:2',
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
