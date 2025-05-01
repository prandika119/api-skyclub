<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RequestRescheduleTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testAddRequestSuccess(): void
    {
        $user = $this->AuthUser();
        $date = now()->addDays(4)->format('Y-m-d');
        $list_booking = $this->paymentBooking($user,$date);
        $response = $this->post('/api/my-booking/'. $list_booking->id."/request-reschedule", [
            'new_schedule_date' => now()->addDays(5)->format('Y-m-d'),
            'new_schedule_time' => '10:00 - 11:00',
            'new_schedule_price' => 50000
        ]);
        dump($response->getContent());
        $response->assertStatus(200);
        $this->assertDatabaseHas('request_reschedules', [
            'old_list_booking_id' => $list_booking->id,
            'user_id' => $user->id
        ]);
    }
    public function testAddRequestMoreThanOldPrice(): void
    {
        $user = $this->AuthUser();
        $date = now()->addDays(4)->format('Y-m-d');
        $list_booking = $this->paymentBooking($user,$date);
        $response = $this->post('/api/my-booking/'. $list_booking->id."/request-reschedule", [
            'new_schedule_date' => now()->addDays(5)->format('Y-m-d'),
            'new_schedule_time' => '10:00 - 11:00',
            'new_schedule_price' => 100000
        ]);
        dump($response->getContent());
        $response->assertStatus(400);
        $response->assertSeeText("Tidak bisa reschedule untuk jadwal yang memiliki harga lebih tinggi");
        $this->assertDatabaseMissing('request_reschedules', [
            'old_list_booking_id' => $list_booking->id,
            'user_id' => $user->id
        ]);
    }

    public function testAddRequestScheduleAlreadyBooked(): void
    {
        $user = $this->AuthUser();
        $date = now()->addDays(5)->format('Y-m-d');
        $list_booking = $this->paymentBooking($user,$date);
        $response = $this->post('/api/my-booking/'. $list_booking->id."/request-reschedule", [
            'new_schedule_date' => now()->addDays(5)->format('Y-m-d'),
            'new_schedule_time' => '8:00 - 9:00',
            'new_schedule_price' => 50000
        ]);
        dump($response->getContent());
        $response->assertStatus(400);
        $response->assertSeeText("Jadwal sudah dibooking oleh orang lain");
        $this->assertDatabaseMissing('request_reschedules', [
            'old_list_booking_id' => $list_booking->id,
            'user_id' => $user->id
        ]);
    }

    public function testGetListReschedule()
    {
        $user = $this->AuthAdmin();
        $listBooking1 = $this->paymentBooking($user, now()->addDays(4)->format('Y-m-d'));
        $listBooking2 = $this->paymentBooking($user, now()->addDays(5)->format('Y-m-d'));
        $this->requestReschedule($listBooking1, $listBooking2, $user);
        $response = $this->get('/api/booking/request-reschedule');
        dump($response->getContent());
        $response->assertStatus(200);
    }

    public function testGetRescheduleEmpty()
    {
        $user = $this->AuthAdmin();
        $response = $this->get('/api/booking/request-reschedule');
        dump($response->getContent());
        $response->assertStatus(200);
    }

    public function testAcceptRescheduleSuccess()
    {
        $user = $this->AuthAdmin();
        $listBooking1 = $this->paymentBooking($user, now()->addDays(4)->format('Y-m-d'));
        $listBooking2 = $this->paymentBooking($user, now()->addDays(5)->format('Y-m-d'));
        $request = $this->requestReschedule($listBooking1, $listBooking2, $user);
        $response = $this->post('/api/booking/'.$request->id.'/accept-reschedule');
        dump($response->getContent());
        $response->assertStatus(200);
        $this->assertDatabaseHas('list_bookings', [
            'id' => $request->new_list_booking_id,
            'status_request' => "Reschedule"
        ]);
    }

    public function testRejectRescheduleSuccess()
    {
        $user = $this->AuthAdmin();
        $listBooking1 = $this->paymentBooking($user, now()->addDays(4)->format('Y-m-d'));
        $listBooking2 = $this->paymentBooking($user, now()->addDays(5)->format('Y-m-d'));
        $request = $this->requestReschedule($listBooking1, $listBooking2, $user);
        $response = $this->post('/api/booking/'.$request->id.'/reject-reschedule');
        dump($response->getContent());
        $response->assertStatus(200);
        $this->assertDatabaseHas('list_bookings', [
            'id' => $request->old_list_booking_id,
            'status_request' => "Reschedule Rejected"
        ]);
        $this->assertDatabaseMissing('request_reschedules', [
            'id' => $request->id,
        ]);
    }
}
