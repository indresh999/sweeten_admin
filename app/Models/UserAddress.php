<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $table    = 'user_addresses';
    public $timestamps  = false;
    protected $fillable = ['user_id','label','address_line','city','state','pincode','lat','lng','is_default'];
    protected $casts    = ['is_default'=>'boolean','lat'=>'float','lng'=>'float'];
}
