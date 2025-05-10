<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RequestCancelTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testAddRequestCancelSuccess(): void
    {
        $user = $this->AuthUser();
        $date = Carbon::now()->addDays(9);
        $listBooking = $this->paymentBooking($user, $date);
        $response = $this->post('/api/my-booking/' . $listBooking->id . '/request-cancel', [
            'reason' => 'test reason',
        ]);
        dump($response->getContent());
        $response->assertStatus(201);
        $this->assertDatabaseHas('request_cancels', [
            'list_booking_id' => $listBooking->id,
            'reason' => 'test reason',
        ]);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function testAddRequestCancelEmptyReason(): void
    {
        $user = $this->AuthUser();
        $date = Carbon::now()->addDays(9);
        $listBooking = $this->paymentBooking($user, $date);
        $response = $this->post('/api/my-booking/' . $listBooking->id . '/request-cancel', [
            'reason' => '',
        ]);
        dump($response->getContent());
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['reason']);
        $this->assertDatabaseCount('notifications', 0);
    }

    public function testAddRequestCancelBookingTimeIsOver(): void
    {
        $user = $this->AuthUser();
        $date = Carbon::now()->addDays(2);
        $listBooking = $this->paymentBooking($user, $date);
        $response = $this->post('/api/my-booking/' . $listBooking->id . '/request-cancel', [
            'reason' => 'test reason',
        ]);
        dump($response->getContent());
        $response->assertStatus(400);
        $this->assertDatabaseMissing('request_cancels', [
            'list_booking_id' => $listBooking->id,
            'reason' => 'test reason',
        ]);
        $this->assertDatabaseCount('notifications', 0);
    }

    public function testGetRequestCancel(): void
    {
        $user = $this->AuthAdmin();
        $date = Carbon::now()->addDays(9);
        $listBooking = $this->paymentBooking($user, $date);
        $this->requestCancel($listBooking, $user);
        $response = $this->get('/api/booking/request-cancel');
        dump($response->getContent());
        $response->assertStatus(200);

    }

    public function testAcceptRequestCancel(): void
    {
        $user = $this->AuthAdmin();
        $date = Carbon::now()->addDays(9);
        $listBooking = $this->paymentBooking($user, $date);
        $requestCancel = $this->requestCancel($listBooking, $user);
        $response = $this->post('/api/booking/' . $requestCancel->id . '/accept-cancel', [
            "reply" => "Diterima",
        ]);
        dump($response->getContent());
        $response->assertStatus(200);
        $this->assertDatabaseHas('list_bookings', [
            'id' => $listBooking->id,
            'status_request' => 'Canceled',
        ]);
        $this->assertDatabaseHas('request_cancels', [
            'id' => $requestCancel->id,
            'reply' => 'Diterima',
        ]);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function testRejectRequestCancel(): void
    {
        $user = $this->AuthAdmin();
        $date = Carbon::now()->addDays(9);
        $listBooking = $this->paymentBooking($user, $date);
        $requestCancel = $this->requestCancel($listBooking, $user);
        $response = $this->post('/api/booking/' . $requestCancel->id . '/reject-cancel', [
            'reply' => 'test reply',
        ]);
        dump($response->getContent());
        $response->assertStatus(200);
        $this->assertDatabaseHas('list_bookings', [
            'id' => $listBooking->id,
            'status_request' => 'Cancel Rejected',
        ]);
        $this->assertDatabaseHas('request_cancels', [
            'id' => $requestCancel->id,
            'reply' => 'test reply',
        ]);
        $this->assertDatabaseCount('notifications', 1);

    }
}
