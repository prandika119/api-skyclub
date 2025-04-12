<?php

namespace Tests\Feature;

use App\Http\Resources\VoucherResource;
use App\Models\Booking;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class BookingTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testAddBooking(): void
    {

        $user = $this->AuthUser();
        $this->addDataToCart();
        $cart = Session::get('cart', []);
        $response = $this->post('/api/booking');
        dump($response->getContent());
        $response->assertStatus(200);
    }

    public function testSuccessPayment()
    {
        $user = $this->AuthUser();
        $user->wallet()->update(['balance' => 100100100]);
        $this->addDataToCart();
        $booking = $this->newBooking($user);
        $response = $this->post('/api/booking/payment', [
            'booking_id' => $booking->id,
        ]);
        dump($response->getContent());
        $response->assertStatus(200);
    }

    public function testSuccessBooking2Items()
    {
        $user = $this->AuthUser();
        $user->wallet()->update(['balance' => 1000000]);
        $this->addDataToCart();
        $booking = $this->newBooking($user);
        $response = $this->post('/api/booking/payment', [
            'booking_id' => $booking->id,
        ]);
        dump($response->getContent());
        $response->assertStatus(200);
        $user->refresh();
        $this->assertEquals(900000, $user->wallet->balance);
    }

    public function testTimeoutPayment()
    {
        // Mock the current time
        Carbon::setTestNow(Carbon::now());

        // Authenticate the user
        $user = $this->AuthUser();
        $user->wallet()->update(['balance' => 1000000]);
        // Add data to the cart
        $this->addDataToCart();

        // Create a new booking with an expired payment time
        $booking = Booking::create([
            'order_date' => now(),
            'rented_by' => $user->id,
            'expired_at' => now()->subMinutes(1), // Set expired time in the past
        ]);

        // Attempt to make a payment
        $response = $this->post('/api/booking/payment', [
            'booking_id' => $booking->id,
        ]);

        // Assert the response
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Bad Request',
            'errors' => 'Waktu Pembayaran Sudah Habis',
        ]);
        $this->assertEquals(1000000, $user->wallet->balance);

        // Reset the mocked time
        Carbon::setTestNow();
    }

    public function testConflictSchedule()
    {
        $user = $this->AuthUser();
        $booking_first = $this->paymentBooking($user, '2025-04-25');
        $user->wallet()->update(['balance' => 1000000]);
        // Add data to the cart
        $this->addDataToCart();
        $booking = $this->newBooking($user);
        $response = $this->post('/api/booking/payment', [
            'booking_id' => $booking->id,
        ]);
        dump($response->getContent());
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Bad Request',
            'errors' => 'Jadwal sudah dibooking oleh orang lain',
        ]);
    }

    public function testNotEnoughBalance()
    {
        // Authenticate the user
        $user = $this->AuthUser();

        // Set wallet balance to an insufficient amount
        $user->wallet()->update(['balance' => 5000]);

        // Add data to the cart
        $this->addDataToCart();

        // Create a new booking
        $booking = $this->newBooking($user);

        // Attempt to make a payment
        $response = $this->post('/api/booking/payment', [
            'booking_id' => $booking->id,
        ]);

        // Assert the response
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Bad Request',
            'errors' => 'Saldo tidak mencukupi',
        ]);

        // Ensure the wallet balance remains unchanged
        $this->assertEquals(5000, $user->wallet->balance);
    }

    /**
     * Test when the booking is use voucher
     */
    public function testBookingUseVoucher()
    {
        $user = $this->AuthUser();
        $user->wallet()->update(['balance' => 1000000]); // satu juta
        $this->addDataToCart();
        $booking = $this->newBooking($user);

        $cart = Session::get('cart', []);
        $voucher = Voucher::where('code', 'DISCOUNT10')->first();
        $cart['voucher'] = new VoucherResource($voucher);
        $cart['discount'] = 10000;
        $cart['total_price'] = 90000;
        Session::put('cart', $cart);

        $this->post('/api/voucher/' . $voucher . '/check');
        $response = $this->post('/api/booking/payment', [
            'booking_id' => $booking->id,
        ]);
        dump($response->getContent());
        dump(Session::get('cart', []));
        $response->assertStatus(200);
        $user->refresh();
        $this->assertEquals(910000, $user->wallet->balance);
    }
}
