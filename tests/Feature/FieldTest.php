<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Field;
use App\Models\FieldImage;
use Database\Seeders\FacilitySeeder;
use Database\Seeders\FieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FieldTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testSuccessCreateField(): void
    {
        $this->AuthAdmin();
        $response = $this->post('/api/fields', [
            'name' => 'fieldTest',
            'description' => 'Description addressTest',
            'weekday_price' => 100000,
            'weekend_price' => 100000,
        ]);
        dump($response->getContent());
        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Field created successfully'
        ]);
    }

    public function testUnauthorizeCreateField(): void
    {
        $this->AuthUser();
        $response = $this->post('/api/fields', [
            'name' => 'fieldTest',
            'description' => 'Description addressTest',
            'weekday_price' => 100000,
            'weekend_price' => 100000,
        ]);
        dump($response->getContent());
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Unauthorized'
        ]);
    }

    public function testSuccessUpdateField(): void
    {
        $this->seed([FieldSeeder::class]);
        $field = Field::where('name', 'fieldTest')->first();
        $this->AuthAdmin();
        $response = $this->put('/api/fields/'. $field->id, [
            'name' => 'fieldTestUpdate',
            'description' => 'Description addressTest',
            'weekday_price' => 100000,
            'weekend_price' => 100000,
        ]);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Field updated successfully'
        ]);
        $this->assertDatabaseHas('fields', [
            'name' => 'fieldTestUpdate'
        ]);
    }

    public function testUnauthorizeUpdateField(): void
    {
        $this->seed([FieldSeeder::class]);
        $field = Field::where('name', 'fieldTest')->first();
        $this->AuthUser();
        $response = $this->put('/api/fields/'. $field->id, [
            'name' => 'fieldTestUpdate',
            'description' => 'Description addressTest',
            'weekday_price' => 100000,
            'weekend_price' => 100000,
        ]);
        dump($response->getContent());
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Unauthorized'
        ]);
    }

    public function testSuccessGetFieldById(): void
    {
        $this->seed([FieldSeeder::class]);
        $field = Field::where('name', 'fieldTest')->first();
        $this->AuthAdmin();
        $response = $this->get('/api/fields/'. $field->id);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Success',
            'data' => [
                'name' => 'fieldTest'
            ]
        ]);
    }
    public function testSuccessGetCompleteFieldById(): void
    {
        $field = $this->CreateCompleteDataField();
        $this->AuthAdmin();
        $response = $this->get('/api/fields/'. $field->id);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Success',
            'data' => [
                'name' => 'fieldTest'
            ]
        ]);
    }

    // testing Field Image
    public function testSuccessAddImageField(): void
    {
        $this->seed([FieldSeeder::class]);
        $field = Field::where('name', 'fieldTest')->first();
        $this->AuthAdmin();
        $image = UploadedFile::fake()->image('imageTest.jpg');
        $response = $this->post('/api/fields/'. $field->id . '/photos', [
            'photo' => $image,
            'title' => 'imageTest'
        ]);
        $fieldImage = FieldImage::latest()->first();
        dump($response->getContent());
        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Image created successfully'
        ]);
        $this->assertTrue(Storage::disk('public')->exists($fieldImage->photo));
    }

    public function testUnaothorizeAddImageField(): void
    {
        $this->seed([FieldSeeder::class]);
        $field = Field::where('name', 'fieldTest')->first();
        $image = UploadedFile::fake()->image('imageTest.jpg');
        $this->AuthUser();
        $response = $this->post('/api/fields/'. $field->id . '/photos', [
            'photo' => $image,
            'title' => 'imageTest'
        ]);
        dump($response->getContent());
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Unauthorized'
        ]);
    }

    public function testDeleteFieldImage(): void
    {
        $this->AuthAdmin();
        $this->AddImageField();
        $fieldImage = FieldImage::latest()->first();
        $this->assertTrue(Storage::disk('public')->exists($fieldImage->photo));
        $response = $this->delete('/api/fields/photos/'. $fieldImage->id);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Image deleted successfully'
        ]);
        $this->assertFalse(Storage::disk('public')->exists($fieldImage->photo));
    }

    // testing Field Facility
    public function testSuccessAddFacilityToField(): void
    {
        $this->seed([FieldSeeder::class]);
        $this->seed([FacilitySeeder::class]);
        $field = Field::where('name', 'fieldTest')->first();
        $facility = Facility::where('name', 'wifi')->first();
        $this->AuthAdmin();
        $response = $this->post('/api/fields/'. $field->id . '/facilities/'. $facility->id);
        dump($response->getContent());
        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Facility added to field successfully'
        ]);
    }

    public function testUnauthorizeAddFacilityToField(): void
    {
        $this->seed([FieldSeeder::class]);
        $this->seed([FacilitySeeder::class]);
        $field = Field::where('name', 'fieldTest')->first();
        $facility = Facility::where('name', 'wifi')->first();
        $this->AuthUser();
        $response = $this->post('/api/fields/' . $field->id . '/facilities/' . $facility->id);
        dump($response->getContent());
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Unauthorized'
        ]);
    }

    public function testSuccessDeleteFacilityFromField(): void
    {
        $this->seed([FieldSeeder::class]);
        $this->seed([FacilitySeeder::class]);
        $field = Field::where('name', 'fieldTest')->first();
        $facility = Facility::where('name', 'wifi')->first();
        $field->facilities()->attach($facility->id);
        $this->AuthAdmin();
        $response = $this->delete('/api/fields/' . $field->id . '/facilities/' . $facility->id);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Facility removed from field successfully'
        ]);
    }

    public function testUnauthorizeDeleteFacilityFromField(): void
    {
        $this->seed([FieldSeeder::class]);
        $this->seed([FacilitySeeder::class]);
        $field = Field::where('name', 'fieldTest')->first();
        $facility = Facility::where('name', 'wifi')->first();
        $field->facilities()->attach($facility->id);
        $this->AuthUser();
        $response = $this->delete('/api/fields/' . $field->id . '/facilities/' . $facility->id);
        dump($response->getContent());
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Unauthorized'
        ]);
    }

}
