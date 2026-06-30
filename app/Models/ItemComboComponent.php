<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemComboComponent extends Model
{
    protected $table = 'item_combo_components';

    protected $fillable = [
        'combo_id', 'item_id', 'variant_id', 'quantity', 'display_label',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function combo()
    {
        return $this->belongsTo(Item::class, 'combo_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id')
            ->select(['id', 'item_name', 'price', 'offer_price', 'images', 'status', 'is_veg']);
    }

    public function variant()
    {
        return $this->belongsTo(ItemVariant::class, 'variant_id')
            ->select(['id', 'item_id', 'label', 'price', 'offer_price']);
    }
}
