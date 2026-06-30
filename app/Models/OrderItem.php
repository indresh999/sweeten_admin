<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table    = 'order_items';
    public $timestamps  = false;
    protected $fillable = ['order_id','item_id','variant_id','quantity','price','offer_price','item_total','gst_amount','item'];
    protected $casts    = ['price'=>'decimal:2','offer_price'=>'decimal:2','item_total'=>'decimal:2','gst_amount'=>'decimal:2','item'=>'array'];

    public function item()   { return $this->belongsTo(Item::class); }
    public function variant(){ return $this->belongsTo(ItemVariant::class); }
}
