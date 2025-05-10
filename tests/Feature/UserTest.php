<?php

namespace Tests\Feature;

use App\Notifications\SuccessBookingNotification;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testUpdateUserSuccess(): void
    {
        $user = $this->AuthUser();
        $file = UploadedFile::fake()->image('profile_photo.jpg');
        $response = $this->patch('/api/users/current',
            [
            'name' => 'John Doe',
            'email' => 'test@gmail.com',
            'no_telp' => '081234567890',
            'team' => 'Backend',
                'address' => 'Jl. Raya Bogor',
                'date_of_birth' => '1999-12-12',
                'profile_photo' => $file
        ]);
        dump("isinya ini ".$response->getContent());
        $response->assertStatus(200);
        $user->refresh();
        dump($user->profile_photo);
        $this->assertEquals('John Doe', $user->name);
        $this->assertNotNull($user->profile_photo);
    }

    public function testGetNotifications()
    {
        $user = $this->AuthUser();
        $listbooking = $this->paymentBooking($user, now());
        $user->notify(new SuccessBookingNotification($listbooking->booking));
        $response = $this->get('/api/users/current/notifications');
        dump($response->getContent());
        $response->assertStatus(200);
    }

    public function testReadNotification()
    {
        $user = $this->AuthUser();
        $listbooking = $this->paymentBooking($user, now());
        $user->notify(new SuccessBookingNotification($listbooking->booking));

        $notification = $user->unreadNotifications->first();

        $response = $this->post("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Notification marked as read',
        ]);

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
