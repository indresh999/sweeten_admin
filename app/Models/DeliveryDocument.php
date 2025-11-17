<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryDocument extends Model
{
    use HasFactory;

    protected $table = 'delivery_documents';

    protected $fillable = [
        'delivery_boy_id',
        'doc_type',
        'file_path',
        'status',
        'remarks',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function deliveryBoy()
    {
        return $this->belongsTo(DeliveryBoy::class);
    }
}