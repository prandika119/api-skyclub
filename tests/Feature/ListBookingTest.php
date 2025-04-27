<?php

namespace Tests\Feature;

use App\Models\SparingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use function Sodium\add;

class ListBookingTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testMyBookingNoSparing(): void
    {
        $user = $this->AuthUser();
        $this->paymentBooking($user, now()->addDay(1));
        $this->paymentBooking($user, now()->addDay(2));
        $response = $this->get('/api/my-booking');
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Booking List',
            'data' => [
                'bookings' => [

                ]
            ]
        ]);
    }

    public function testMyBookingSparingNoRequest(): void
    {
        $user = $this->AuthUser();
        $this->createSparing($user, now()->addDay(1));
        $this->createSparing($user, now()->addDay(2));
        $response = $this->get('/api/my-booking?sparing=1');
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Booking List',
            'data' => [
                'bookings' => [

                ]
            ]
        ]);
    }

    public function testMyBookingSparingHaveRequest(): void
    {
        $user2 = $this->AuthUser2();
        $user = $this->AuthUser();
        $sparing = $this->createSparing($user, now()->addDay(1));
        $this->createSparing($user, now()->addDay(2));
        SparingRequest::create([
            'sparing_id' => $sparing->id,
            'user_id' => $user2->id,
            'status' => 'waiting'
        ]);
        $response = $this->get('/api/my-booking?sparing=1');
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Booking List',
            'data' => [
                'bookings' => [

                ]
            ]
        ]);
    }

    public function testHistorySparingSubmit(): void
    {
        $user2 = $this->AuthUser2();
        $user = $this->AuthUser();
        $sparing = $this->createSparing($user2, now()->addDay(1));
        $this->createSparing($user2, now()->addDay(2));
        SparingRequest::create([
            'sparing_id' => $sparing->id,
            'user_id' => $user->id,
            'status' => 'waiting'
        ]);
        $response = $this->get('/api/my-booking?sparing=1');
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Booking List',
            'data' => [
                'bookings' => [

                ]
            ]
        ]);
    }

    public function testHistoryBookingMustEmpty()
    {
        $user2 = $this->AuthUser2();
        $user = $this->AuthUser();
        $sparing = $this->createSparing($user, now()->addDay(1));
        $this->createSparing($user, now()->addDay(2));
        SparingRequest::create([
            'sparing_id' => $sparing->id,
            'user_id' => $user2->id,
            'status' => 'waiting'
        ]);
        $response = $this->get('/api/my-booking?past=1');
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Booking List Past',
            'data' => [
                'bookings' => [

                ]
            ]
        ]);
    }

    public function testHistoryBooking()
    {
        $user2 = $this->AuthUser2();
        $user = $this->AuthUser();
        $sparing = $this->createSparing($user, now()->subDay(1));
        $this->createSparing($user, now()->subDay(2));
        SparingRequest::create([
            'sparing_id' => $sparing->id,
            'user_id' => $user2->id,
            'status' => 'waiting'
        ]);
        $response = $this->get('/api/my-booking?past=1');
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Booking List Past',
            'data' => [
                'bookings' => [

                ]
            ]
        ]);
    }

}
