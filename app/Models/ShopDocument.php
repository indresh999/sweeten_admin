<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopDocument extends Model
{
    protected $table = 'shop_documents';

    protected $fillable = [
        'shop_id', 'doc_type', 'doc_label',
        'file_path', 'original_name', 'file_size', 'mime_type',
        'status', 'remarks',
    ];

    public static array $knownTypes = [
        'fssai_certificate' => 'FSSAI Certificate',
        'gst_certificate'   => 'GST Certificate',
        'pan_card'          => 'PAN Card',
        'trade_licence'     => 'Trade Licence',
        'cancelled_cheque'  => 'Cancelled Cheque',
        'other'             => 'Other Document',
    ];

    public function shop()
    {
        return $this->belongsTo(AppOwnerUser::class, 'shop_id', 'shop_id');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function getLabelAttribute(): string
    {
        return $this->doc_label
            ?? (static::$knownTypes[$this->doc_type] ?? ucfirst(str_replace('_', ' ', $this->doc_type)));
    }
}
