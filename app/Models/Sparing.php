<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sparing extends Model
{
    use HasFactory;
    protected $fillable = [
        'list_booking_id',
        'description',
        'status',
        'created_by',
    ];

    public function listBooking()
    {
        return $this->belongsTo(ListBooking::class, 'list_booking_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sparingRequest()
    {
        return $this->hasMany(SparingRequest::class);
    }
}
