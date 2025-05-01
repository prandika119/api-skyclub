<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestReschedule extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function newListBooking(): BelongsTo
    {
        return $this->belongsTo(ListBooking::class, 'new_list_booking_id');
    }
    public function oldListBooking(): BelongsTo
    {
        return $this->belongsTo(ListBooking::class, 'old_list_booking_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
