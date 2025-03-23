<?php

namespace Database\Seeders;

use App\Models\Field;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $field = Field::create([
            'name' => 'fieldTest',
            'description' => 'Ini adalah lapangan sky club yang nyaman',
            'weekday_price' => 50000,
            'weekend_price' => 75000
        ]);
    }
}
