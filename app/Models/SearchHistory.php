<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchHistory extends Model
{
    protected $table = 'search_history';
    public $timestamps = false;
    protected $fillable = ['user_id','query','result_count','created_at'];
}
