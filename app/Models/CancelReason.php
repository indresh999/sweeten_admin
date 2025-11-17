<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CancelReason extends Model
{
    protected $table = 'cancel_reasons';

    protected $fillable = ['reason', 'is_active'];

    public function orders()
    {
        return $this->hasMany(Order::class, 'cancel_reason_id');
    }
}