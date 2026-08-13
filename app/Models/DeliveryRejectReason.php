<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryRejectReason extends Model
{
    protected $table = 'delivery_reject_reasons';
    protected $fillable = ['reason', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
