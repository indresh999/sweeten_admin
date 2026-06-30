<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemSubcategory extends Model
{
    use SoftDeletes;

    protected $table = 'item_subcategories';

    protected $fillable = [
        'category_id','parent_id','name','slug','description','image',
        'status','sort_order','is_featured',
        'hsn_code','sac_code',
        'cgst_percent','sgst_percent','igst_percent','cess_percent',
        'gst_percent','is_tax_inclusive',
        'commission_percent','commission_type',
    ];

    protected $casts = [
        'status'=>'integer','is_featured'=>'integer','sort_order'=>'integer',
        'cgst_percent'=>'float','sgst_percent'=>'float','igst_percent'=>'float',
        'cess_percent'=>'float','gst_percent'=>'float','is_tax_inclusive'=>'boolean',
        'commission_percent'=>'float',
    ];

    public function category()
    {
        return $this->belongsTo(ItemCategory::class,'category_id');
    }

    public function parent()
    {
        return $this->belongsTo(ItemSubcategory::class,'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ItemSubcategory::class,'parent_id')->where('status',1)->orderBy('sort_order');
    }

    public function items()
    {
        return $this->hasMany(Item::class,'subcategory_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset($this->image) : null;
    }

    public function getEffectiveTaxAttribute(): array
    {
        $cgst = (float)($this->cgst_percent ?? $this->category?->cgst_percent ?? 0);
        $sgst = (float)($this->sgst_percent ?? $this->category?->sgst_percent ?? 0);
        $igst = (float)($this->igst_percent ?? $this->category?->igst_percent ?? 0);
        $cess = (float)($this->cess_percent ?? $this->category?->cess_percent ?? 0);
        return ['cgst'=>round($cgst,2),'sgst'=>round($sgst,2),'igst'=>round($igst,2),'cess'=>round($cess,2),'total'=>round($cgst+$sgst+$cess,2)];
    }
}
