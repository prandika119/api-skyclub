<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'expire_date',
        'quota',
        'discount_price',
        'discount_percentage',
        'max_discount',
        'min_price'
    ];

    public function isExpired()
    {
        $currentDate = Carbon::now();
        $expiredDate = Carbon::parse($this->expire_date);

        if ($currentDate->greaterThan($expiredDate)) {
            return true;
        }
        return false;
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
