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
        $facilities = ['parking', 'mushola', 'toilet', 'medical', 'security', 'tribune', 'wifi', 'shower', 'locker', 'gym', 'canteen', 'sauna', 'run'];
        foreach ($facilities as $facility) {
            Facility::create([
                'name' => $facility,
                'photo' => $facility . '.jpg',
            ]);
        }
    }
}
