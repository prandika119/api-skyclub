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
}
