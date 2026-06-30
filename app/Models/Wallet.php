<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table    = 'wallets';
    protected $fillable = ['user_id', 'balance'];
    protected $casts    = ['balance' => 'decimal:2'];

    public function user()
    {
        return $this->belongsTo(AppUser::class, 'user_id');
    }

    public function credit(float $amount, string $note = '', string $type = 'credit', int $refId = null): void
    {
        $this->increment('balance', $amount);
        WalletTransaction::create([
            'wallet_id'      => $this->id,
            'user_id'        => $this->user_id,
            'amount'         => $amount,
            'type'           => 'credit',
            'note'           => $note,
            'reference_type' => $type,
            'reference_id'   => $refId,
            'balance_after'  => (float) $this->fresh()->balance,
        ]);
    }

    public function debit(float $amount, string $note = '', string $type = 'debit', int $refId = null): void
    {
        $this->decrement('balance', $amount);
        WalletTransaction::create([
            'wallet_id'      => $this->id,
            'user_id'        => $this->user_id,
            'amount'         => $amount,
            'type'           => 'debit',
            'note'           => $note,
            'reference_type' => $type,
            'reference_id'   => $refId,
            'balance_after'  => (float) $this->fresh()->balance,
        ]);
    }
}
