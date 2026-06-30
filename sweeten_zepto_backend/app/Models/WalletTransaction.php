<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $table = 'wallet_transactions';
    public $timestamps = false;

    protected $fillable = ['user_id','type','amount','description','reference_type','reference_id','balance_after'];
    protected $casts = ['amount' => 'decimal:2', 'balance_after' => 'decimal:2'];
}
