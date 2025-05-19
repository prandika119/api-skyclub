<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'description',
        'payment',
        'banner',
        'slider_1',
        'slider_2',
        'slider_3'
    ];
    protected $casts = [
        'payment' => 'array',
    ];


}
