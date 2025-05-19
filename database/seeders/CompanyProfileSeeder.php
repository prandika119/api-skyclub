<?php

namespace Database\Seeders;

use App\Models\CompanyProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CompanyProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): CompanyProfile
    {
        // Ensure the storage directory exists
        Storage::disk('public')->makeDirectory('company_profile');

        // Create dummy image files
        $logoPath = 'company_profile/logo.png';
        $bannerPath = 'company_profile/banner.png';
        $slider1Path = 'company_profile/slider_1.png';
        $slider2Path = 'company_profile/slider_2.png';
        $slider3Path = 'company_profile/slider_3.png';

        Storage::disk('public')->put($logoPath, file_get_contents(public_path('dummy/logo.png')));
        Storage::disk('public')->put($bannerPath, file_get_contents(public_path('dummy/banner.png')));
        Storage::disk('public')->put($slider1Path, file_get_contents(public_path('dummy/slider_1.png')));
        Storage::disk('public')->put($slider2Path, file_get_contents(public_path('dummy/slider_2.png')));
        Storage::disk('public')->put($slider3Path, file_get_contents(public_path('dummy/slider_3.png')));

        // Seed the company_profiles table
        $companyProfile = CompanyProfile::create([
            'name' => 'Dummy Company',
            'address' => '123 Dummy Street',
            'no_telp' => '1234567890',
            'email' => 'dummy@company.com',
            'description' => 'This is a dummy company profile.',
            'payment' => json_encode(['bank_transfer', 'credit_card']),
            'logo' => $logoPath,
            'banner' => $bannerPath,
            'slider_1' => $slider1Path,
            'slider_2' => $slider2Path,
            'slider_3' => $slider3Path,
        ]);
        return $companyProfile;
    }
}
