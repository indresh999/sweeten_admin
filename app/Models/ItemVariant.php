<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemVariant extends Model
{
    protected $table = 'item_variants';

    protected $fillable = [
        'item_id', 'label', 'price', 'offer_price',
        'min_quantity', 'gst_percent', 'cgst', 'sgst', 'igst',
        'hsn_code', 'is_default', 'status',
    ];

    protected $casts = [
        'price'       => 'float',
        'offer_price' => 'float',
        'gst_percent' => 'float',
        'is_default'  => 'boolean',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function effectivePrice(): float
    {
        return (float) ($this->offer_price ?? $this->price ?? 0);
    }
}
