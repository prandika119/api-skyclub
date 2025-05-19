<?php

namespace Tests\Feature;

use Database\Seeders\CompanyProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CompanyProfileTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testUpdateSuccess(): void
    {
        $user = $this->AuthAdmin();
        $companyProfile = $this->seed(CompanyProfileSeeder::class);
        $respons = $companyProfile->put('/api/settings', [
            'name' => 'New Company Name',
            'address' => 'New Address',
            'no_telp' => '0987654321',
            'email' => 'company@update.com',
            'description' => 'Updated description'
        ]);
        dump($respons->getContent());
        $respons->assertStatus(200);
    }

    public function testUpdateLogoSuccess(): void
    {
        $this->AuthAdmin();
        $companyProfile = $this->seed(CompanyProfileSeeder::class);
        $image = UploadedFile::fake()->image('logo.png');
        $respons = $companyProfile->post('/api/settings/logo', [
            'logo' => $image
        ]);
        dump($respons->getContent());
        $respons->assertStatus(200);
    }

    public function testUpdateBanner()
    {
        $this->AuthAdmin();
        $companyProfile = $this->seed(CompanyProfileSeeder::class);
        $image = UploadedFile::fake()->image('banner.png');
        $respons = $companyProfile->post('/api/settings/banner', [
            'banner' => $image
        ]);
        dump($respons->getContent());
        $respons->assertStatus(200);
    }

    public function testGetSetting()
    {
        $this->AuthAdmin();
        $companyProfile = $this->seed(CompanyProfileSeeder::class);
        $respons = $companyProfile->get('/api/settings');
        dump($respons->getContent());
        $respons->assertStatus(200);
    }

    public function testUpdateSlider()
    {
        $this->AuthAdmin();
        $companyProfile = $this->seed(CompanyProfileSeeder::class);
        $image = UploadedFile::fake()->image('slider_update.png');
        $image2 = UploadedFile::fake()->image('slider2_update.png');
        $image3 = UploadedFile::fake()->image('slider3_update.png');
        $respons = $companyProfile->post('/api/settings/slider', [
            'slider' => [
                $image,
                $image2,
                $image3
            ]
        ]);
        dump($respons->getContent());
        $respons->assertStatus(200);
    }

}
