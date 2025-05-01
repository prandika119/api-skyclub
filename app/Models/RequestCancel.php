<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestCancel extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    //protected $with = ['booking', 'user', 'field'];

    public function listBooking()
    {
        return $this->belongsTo(ListBooking::class, 'list_booking_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
