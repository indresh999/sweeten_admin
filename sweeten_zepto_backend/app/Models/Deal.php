<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Deal extends Model
{
    protected $fillable = [
        'title','subtitle','banner_image','deal_type',
        'discount_type','discount_value','starts_at','ends_at','is_active',
    ];

    protected $casts = [
        'starts_at'      => 'datetime',
        'ends_at'        => 'datetime',
        'discount_value' => 'decimal:2',
        'is_active'      => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(DealItem::class);
    }

    public function dealItems()
    {
        return $this->hasMany(DealItem::class)->with('item.variants');
    }

    public function isActive(): bool
    {
        return $this->is_active && Carbon::now()->between($this->starts_at, $this->ends_at);
    }
}
