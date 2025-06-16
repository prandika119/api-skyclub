<?php

namespace Tests;

use App\Models\Booking;
use App\Models\Facility;
use App\Models\Field;
use App\Models\FieldImage;
use App\Models\ListBooking;
use App\Models\RecentTransaction;
use App\Models\RequestCancel;
use App\Models\RequestReschedule;
use App\Models\Schedule;
use App\Models\Sparing;
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
        DB::delete('delete from recent_transactions');
        DB::delete('delete from notifications');
        DB::delete('delete from request_cancels');
        DB::delete('delete from request_reschedules');
        DB::delete('delete from reviews');
        DB::delete('delete from articles');
        DB::delete('delete from sparing_requests');
        DB::delete('delete from sparings');
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
        $directoryGeneralImage = storage_path('app/public/company_profile');
        File::cleanDirectory($directoryProfilePhoto);
        File::cleanDirectory($directoryFieldImage);
        File::cleanDirectory($directoryGeneralImage);
        Session::forget('cart');
    }

    /**
     * Login User
     */
    protected function AuthUser(): User
    {
        $this->seed([UserSeeder::class]);
        $user = User::where('username', 'test')->first();
        $this->actingAs($user);
        return $user;
    }

    protected function AuthUser2()
    {
        $user = User::create([
            'name' => 'test2',
            'username' => 'test2',
            'email' => 'test2@gmail.com',
            'no_telp' => '082234567891',
            'password' => bcrypt('password')
        ]);
        $user->wallet()->create([
            'balance' => 0,
        ]);
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
        return Field::where('name', 'Lapangan Mini Soccer SkyClub 1')->first();
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

    /**
     * Create a new booking & payment successfully
     */
    protected function paymentBooking($user, $date): ListBooking
    {
        $field = $this->CreateDataField();
        $booking = Booking::create([
            'order_date' => now()->subDay(1),
            'rented_by' => $user->id,
            'expired_at' => now()->addMinutes(5),
            'status' => 'accepted',
        ]);
        $list_booking = ListBooking::create([
            'date' => Carbon::parse($date),
            'session' => '8:00 - 9:00',
            'price' => '50000',
            'field_id' => $field->id,
            'booking_id' => $booking->id
        ]);
        return $list_booking;
    }

    /**
     * Logic navigate to payment page
     */
    protected function logicPaymentPage(): Booking
    {
        $user = $this->AuthUser();
        $user->wallet()->update(['balance' => 1000000]);
        $this->addDataToCart();
        $booking = $this->newBooking($user);
        return $booking;
    }

    protected function createSparing(User $user, $date): Sparing
    {
        $listBooking = $this->paymentBooking($user, $date);
        $sparing = Sparing::create([
            'list_booking_id' => $listBooking->id,
            'description' => 'Sparing Test',
            'status' => 'waiting',
            'created_by' => $user->id
        ]);
        return $sparing;
    }

    protected function requestCancel(ListBooking $listBooking, User $user)
    {
        $listBooking->update([
            'status_request' => 'Cancel Request',
        ]);
        $requestCancel = RequestCancel::create([
            'list_booking_id' => $listBooking->id,
            'user_id' => $user->id,
            'reason' => "test reason",
        ]);
        return $requestCancel;
    }

    protected function requestReschedule(ListBooking $listBooking1, ListBooking $listBooking2, User $user)
    {
        $listBooking1->update([
            'status_request' => 'Reschedule Request',
        ]);
        $requestReschedule = RequestReschedule::create([
            'old_list_booking_id' => $listBooking1->id,
            'user_id' => $user->id,
            'new_list_booking_id' => $listBooking2->id,
        ]);
        return $requestReschedule;
    }

    protected function topupWallet(User $user, $amount): RecentTransaction
    {
        $user->wallet()->update([
            'balance' => $amount,
        ]);

        $recent = RecentTransaction::create([
            'user_id' => $user->id,
            'wallet_id' => $user->wallet->id,
            'transaction_type' => 'topup',
            'amount' => $amount,
            'bank_ewallet' => 'Bank BCA',
            'number' => '1234567890',
        ]);
        return $recent;
    }
}
