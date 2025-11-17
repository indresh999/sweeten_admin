<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'address_line',
        'city',
        'state',
        'pincode',
        'lat',
        'lng',
        'is_default'
    ];

    public function user()
    {
        return $this->belongsTo(AppUser::class, 'user_id');
    }
}