<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryCashSubmission extends Model
{
    protected $table = 'delivery_cash_submissions';

    protected $fillable = [
        'delivery_boy_id',
        'amount',
        'screenshot_path',
        'status',
        'submission_date',
        'admin_notes',
        'verified_at',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    public function boy()
    {
        return $this->belongsTo(DeliveryBoy::class, 'delivery_boy_id');
    }
}
