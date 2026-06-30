<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table    = 'banners';
    protected $fillable = ['title','image_url','banner_type','target_type','target_id','target_url','start_date','end_date','status','sort_order','click_count','is_sponsored'];
    protected $casts    = ['is_sponsored'=>'boolean'];
}
