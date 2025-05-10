<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SparingRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'sparing_id',
        'user_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function sparing()
    {
        return $this->belongsTo(Sparing::class);
    }
}
