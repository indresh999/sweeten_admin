<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    protected $table = 'product_units';

    protected $fillable = ['name', 'short_name', 'category', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
