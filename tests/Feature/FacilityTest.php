<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Field;
use Database\Seeders\FacilitySeeder;
use Database\Seeders\FieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FacilityTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testGetAllFacility(): void
    {
        $this->seed([FieldSeeder::class]);
        $this->seed([FacilitySeeder::class]);
        $this->AuthUser();
        $response = $this->get('/api/field-facilities');
        dump($response->getContent());
        $response->assertStatus(200);
    }
}
