<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppHomeFilter extends Model
{
    protected $table = 'app_home_filters';

    protected $fillable = [
        'filter_name',
    ];
}