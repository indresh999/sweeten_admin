<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryDocument extends Model
{
    protected $table    = 'delivery_documents';
    public $timestamps  = false;
    protected $fillable = ['delivery_boy_id','doc_type','file_path','status'];

    public function deliveryBoy()
    {
        return $this->belongsTo(DeliveryBoy::class, 'delivery_boy_id');
    }
}
