<?php

namespace Tests\Feature;

use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testAddReview(): void
    {
        // Create a user and a booking
        $user = $this->AuthUser();
        $list_booking = $this->paymentBooking($user, now()->subDays(1));
        $booking = $list_booking->booking;

        // Send a POST request to add a review
        $response = $this->post('/api/reviews', [
            'rating' => 5,
            'comment' => 'Excellent service!',
            'booking_id' => $booking->id,
        ]);
        $response2 = $this->get('/api/my-booking?past=1');
        dump($response2);

        // Assert the response
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Review created successfully',
            ]);

        // Assert the review exists in the database
        $this->assertDatabaseHas('reviews', [
            'rating' => 5,
            'comment' => 'Excellent service!',
            'user_id' => $user->id,
            'booking_id' => $booking->id,
        ]);
    }

    public function testGetReviews(): void
    {
        // Create a user and a booking
        $user = $this->AuthUser();
        $list_booking = $this->paymentBooking($user, now()->subDays(1));
        $booking = $list_booking->booking;

        // Create a review
        $review = Review::create([
            'rating' => 5,
            'comment' => 'Excellent service!',
            'user_id' => $user->id,
            'booking_id' => $booking->id,
        ]);

        // Send a GET request to fetch reviews
        $response = $this->get('/api/reviews');
        dump($response->getContent());
        // Assert the response
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Success',
                'data' => [
                ]
            ]);
    }
}
