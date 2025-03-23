<?php

namespace Tests;

use App\Models\Facility;
use App\Models\Field;
use App\Models\FieldImage;
use App\Models\User;
use Database\Seeders\FacilitySeeder;
use Database\Seeders\FieldSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('delete from users');
        DB::delete('delete from field_images');
        DB::delete('delete from fields_facilities');
        DB::delete('delete from facilities');
        DB::delete('delete from fields');
        $directoryProfilePhoto = storage_path('app/public/profile_photos');
        $directoryFieldImage = storage_path('app/public/fields');
        File::cleanDirectory($directoryProfilePhoto);
        File::cleanDirectory($directoryFieldImage);
    }

    protected function AuthUser(): User
    {
        $this->seed([UserSeeder::class]);
        $user = User::where('username', 'test')->first();
        $this->actingAs($user);
        return $user;
    }

    protected function AuthAdmin(): User
    {
        $this->seed([UserSeeder::class]);
        $user = User::where('username', 'admin')->first();
        $this->actingAs($user);
        return $user;
    }

    protected function AddImageField(): void
    {
        $this->seed([FieldSeeder::class]);
        $field = Field::where('name', 'fieldTest')->first();
        $fieldImage = $field->photos()->create([
            'photo' => 'fields/imageTest.jpg',
            'title' => 'imageTest'
        ]);
        Storage::disk('public')->put($fieldImage->photo, 'dummy content');
    }

    protected function CreateCompleteDataField(): Field
    {
        $this->seed([FieldSeeder::class, FacilitySeeder::class]);

        // Ensure facilities exist
        $facility1 = Facility::where('name', 'Parking')->first();
        $facility2 = Facility::where('name', 'Wifi')->first();

        $field = Field::where('name', 'fieldTest')->first();
        $field->facilities()->attach([$facility1->id, $facility2->id]);
        $field->photos()->create([
            'photo' => 'fields/imageTest.jpg',
            'title' => 'imageTest'
        ]);
        $field->photos()->create([
            'photo' => 'fields/imageTest2.jpg',
            'title' => 'imageTest2'
        ]);
        Storage::disk('public')->put('fields/imageTest.jpg', 'dummy content');
        Storage::disk('public')->put('fields/imageTest2.jpg', 'dummy content');
        return $field;
    }

//    protected function CreateCompleteDataField(): Field
//    {
//        $this->seed([FieldSeeder::class, FacilitySeeder::class]);
//        $field = Field::where('name', 'fieldTest')->first();
//        $field->facilities()->attach([1, 2]);
//        $field->photos()->create([
//            'photo' => 'fields/imageTest.jpg',
//            'title' => 'imageTest'
//        ]);
//        $field->photos()->create([
//            'photo' => 'fields/imageTest2.jpg',
//            'title' => 'imageTest2'
//        ]);
//        Storage::disk('public')->put('fields/imageTest.jpg', 'dummy content');
//        Storage::disk('public')->put('fields/imageTest2.jpg', 'dummy content');
//        return $field;
//    }
}
