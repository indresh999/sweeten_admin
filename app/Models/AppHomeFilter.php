<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppHomeFilter extends Model
{
    protected $table    = 'app_home_filters';
    public $timestamps  = false;
    protected $fillable = ['label','filter_key','filter_value','icon','sort_order','is_active'];
}
