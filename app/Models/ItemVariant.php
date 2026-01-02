<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemVariant extends Model
{
    protected $table = 'item_variants';

    protected $fillable = [
        'item_id',
        'label',
        'price',
        'offer_price',
        'min_quantity',

        'gst_percent',
        'cgst',
        'sgst',
        'igst',
        'hsn_code',

        'is_default',
        'status',
    ];

    protected $casts = [
        'is_default'  => 'boolean',
        'price'       => 'decimal:2',
        'offer_price' => 'decimal:2',
        'gst_percent' => 'decimal:2',
        'cgst'        => 'decimal:2',
        'sgst'        => 'decimal:2',
        'igst'        => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Auto-calculate CGST & SGST if not provided
     */
    protected static function booted()
    {
        static::saving(function ($variant) {

            if ($variant->gst_percent && (!$variant->cgst || !$variant->sgst)) {
                $half = $variant->gst_percent / 2;
                $variant->cgst = $half;
                $variant->sgst = $half;
                $variant->igst = null;
            }
        });
    }
}