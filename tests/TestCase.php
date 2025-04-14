<?php

namespace Tests;

use App\Models\Booking;
use App\Models\Facility;
use App\Models\Field;
use App\Models\FieldImage;
use App\Models\ListBooking;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\FacilitySeeder;
use Database\Seeders\FieldSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('delete from list_bookings');
        DB::delete('delete from bookings');
        DB::delete('delete from users');
        DB::delete('delete from field_images');
        DB::delete('delete from fields_facilities');
        DB::delete('delete from facilities');
        DB::delete('delete from fields');
        DB::delete('delete from wallets');
        DB::delete('delete from vouchers');
        $directoryProfilePhoto = storage_path('app/public/profile_photos');
        $directoryFieldImage = storage_path('app/public/fields');
        File::cleanDirectory($directoryProfilePhoto);
        File::cleanDirectory($directoryFieldImage);
        Session::forget('cart');
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

    protected function CreateDataField(): Field
    {
        $this->seed([FieldSeeder::class]);
        return Field::where('name', 'fieldTest')->first();
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

    protected function addDataToCart()
    {
        Session::get('cart', []);
        $field = $this->CreateDataField();

        $schedule = new Schedule('2025-04-25', $field);
        $schedule2 = new Schedule('2025-04-25', $field);
        $this->post('/api/cart', [
        'field_id' => $schedule->field,
        'schedule_date' => $schedule->date,
        'schedule_time' => '8:00 - 9:00',
        'price' => $schedule->price,
        ]);
        $this->post('/api/cart', [
        'field_id' => $schedule2->field,
        'schedule_date' => $schedule2->date,
        'schedule_time' => '9:00 - 10:00',
        'price' => $schedule2->price,
        ]);
        return $schedule->field;
    }

    protected function newBooking(User $user): Booking
    {
        $booking = Booking::create([
            'order_date' => now(),
            'rented_by' => $user->id,
            'expired_at' => now()->addMinutes(5)
        ]);
        return $booking;
    }

    protected function paymentBooking($user, $date): Booking
    {
        $field = $this->CreateDataField();
        $booking = Booking::create([
            'order_date' => now()->subDay(1),
            'rented_by' => $user->id,
            'expired_at' => now()->addMinutes(5)
        ]);
        ListBooking::create([
            'date' => Carbon::parse($date),
            'session' => '8:00 - 9:00',
            'price' => '50000',
            'field_id' => $field->id,
            'booking_id' => $booking->id
        ]);
        return $booking;
    }

    protected function logicPaymentPage(): Booking
    {
        $user = $this->AuthUser();
        $user->wallet()->update(['balance' => 1000000]);
        $this->addDataToCart();
        $booking = $this->newBooking($user);
        return $booking;
    }
}
