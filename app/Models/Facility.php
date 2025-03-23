<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;

    protected $guarded = [];
    public function fields()
    {
        return $this->belongsToMany(Field::class, 'fields_facilities', 'facility_id', 'field_id');
    }
}
