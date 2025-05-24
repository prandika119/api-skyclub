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
            'name' => 'Lapangan Mini Soccer SkyClub 1',
            'description' => 'Ini adalah lapangan Sky Club yang dirancang untuk memberikan pengalaman bermain yang nyaman, bersih, dan luar biasa. Lapangan ini dilengkapi dengan fasilitas modern, permukaan rumput sintetis berkualitas tinggi, serta pencahayaan yang optimal untuk permainan siang maupun malam. Cocok untuk berbagai acara olahraga, latihan rutin, atau pertandingan persahabatan.',
            'weekday_price' => 50000,
            'weekend_price' => 75000
        ]);
    }
}
