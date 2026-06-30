<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemCategory extends Model
{
    use SoftDeletes;

    protected $table = 'item_categories';

    protected $fillable = [
        'category_name','slug','description','image','status','category_type',
        'is_featured','sort_order',
        'hsn_code','sac_code',
        'cgst_percent','sgst_percent','igst_percent','cess_percent',
        'gst_percent','is_tax_inclusive',
        'commission_percent','commission_type',
        'meta_title','meta_description',
    ];

    protected $casts = [
        'status'=>'integer','is_featured'=>'integer','sort_order'=>'integer',
        'cgst_percent'=>'float','sgst_percent'=>'float','igst_percent'=>'float',
        'cess_percent'=>'float','gst_percent'=>'float','is_tax_inclusive'=>'boolean',
        'commission_percent'=>'float',
    ];

    public function subcategories()
    {
        return $this->hasMany(ItemSubcategory::class,'category_id')->where('status',1)->orderBy('sort_order');
    }

    public function allSubcategories()
    {
        return $this->hasMany(ItemSubcategory::class,'category_id')->orderBy('sort_order');
    }

    public function items()
    {
        return $this->hasMany(Item::class,'category_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset($this->image) : null;
    }

    public function getEffectiveTaxAttribute(): array
    {
        $cgst = (float)($this->cgst_percent ?? ($this->gst_percent / 2));
        $sgst = (float)($this->sgst_percent ?? ($this->gst_percent / 2));
        $igst = (float)($this->igst_percent ?? $this->gst_percent);
        $cess = (float)($this->cess_percent ?? 0);
        return ['cgst'=>round($cgst,2),'sgst'=>round($sgst,2),'igst'=>round($igst,2),'cess'=>round($cess,2),'total'=>round($cgst+$sgst+$cess,2)];
    }
}
