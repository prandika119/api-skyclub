<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recipient_id',
        'wallet_id',
        'transaction_type',
        'amount',
        'bank_ewallet',
        'number'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

}
