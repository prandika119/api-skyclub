<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_date',
        'status',
        'rented_by',
        'voucher_id',
        'user_offline',
        'expired_at',
        'voucher_id'
    ];

    public function listBooking()
    {
        return $this->hasMany(ListBooking::class);
    }
}
