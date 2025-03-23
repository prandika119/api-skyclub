<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Facility::create([
            'name' => 'Parking',
            'photo' => 'parking.jpg',
        ]);

        Facility::create([
            'name' => 'Gym',
            'photo' => 'gym.jpg',
        ]);

        Facility::create([
            'name' => 'Wifi',
            'photo' => 'wifi.jpg'
        ]);
    }
}
