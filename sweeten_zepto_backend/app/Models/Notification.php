<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    public $timestamps = false;

    protected $fillable = [
        'user_id','user_type','title','body','type',
        'reference_type','reference_id','is_read','sent_at',
    ];

    protected $casts = ['is_read' => 'boolean'];
}
