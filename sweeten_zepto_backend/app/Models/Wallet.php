<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = ['user_id','balance'];
    protected $casts = ['balance' => 'decimal:2'];

    public function user()
    {
        return $this->belongsTo(AppUser::class, 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'user_id', 'user_id');
    }

    public function credit(float $amount, string $description, string $refType = null, int $refId = null): WalletTransaction
    {
        $this->increment('balance', $amount);
        $this->refresh();
        $txn = WalletTransaction::create([
            'user_id'        => $this->user_id,
            'type'           => 'credit',
            'amount'         => $amount,
            'description'    => $description,
            'reference_type' => $refType,
            'reference_id'   => $refId,
            'balance_after'  => $this->balance,
        ]);
        AppUser::where('id', $this->user_id)->update(['wallet_balance' => $this->balance]);
        return $txn;
    }

    public function debit(float $amount, string $description, string $refType = null, int $refId = null): WalletTransaction
    {
        if ($this->balance < $amount) throw new \Exception('Insufficient wallet balance');
        $this->decrement('balance', $amount);
        $this->refresh();
        $txn = WalletTransaction::create([
            'user_id'        => $this->user_id,
            'type'           => 'debit',
            'amount'         => $amount,
            'description'    => $description,
            'reference_type' => $refType,
            'reference_id'   => $refId,
            'balance_after'  => $this->balance,
        ]);
        AppUser::where('id', $this->user_id)->update(['wallet_balance' => $this->balance]);
        return $txn;
    }
}
