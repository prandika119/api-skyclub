<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ListBooking extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function sparing(): HasOne
    {
        return $this->hasOne(Sparing::class, 'list_booking_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class, 'field_id');
    }
    public function requestCancel(): HasOne
    {
        return $this->hasOne(RequestCancel::class, 'list_booking_id');
    }


}
