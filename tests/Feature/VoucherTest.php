<?php

namespace Tests\Unit;

use App\Http\Resources\VoucherResource;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class VoucherTest extends TestCase
{
    /**
     * Test when the voucher is not found.
     */
    public function testCheckVoucherNotFound()
    {
        $booking = $this->logicPaymentPage();
        $response = $this->post('/api/voucher/INVALID_C');

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Voucher tidak ditemukan',
            'data' => null,
        ]);
        dump($response->getContent());
        dump(Session::get('cart', []));
    }

    /**
     * Test when the voucher is expired.
     */
    public function testCheckVoucherExpired()
    {
        $booking = $this->logicPaymentPage();
        $voucher = Voucher::where('code', 'EXPIREDVOUCHER')->first();

        $response = $this->post('/api/voucher/' . $voucher->code );
        dump($response->getContent());
        dump(Session::get('cart', []));
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Voucher sudah kadaluarsa',
            'data' => null,
        ]);
    }

    /**
     * Test when the voucher quota is zero.
     */
    public function testCheckVoucherQuotaZero()
    {
        $booking = $this->logicPaymentPage();
        $voucher = Voucher::where('code', 'NOQUOTA')->first();

        $response = $this->post('/api/voucher/' . $voucher->code );
        dump($response->getContent());
        dump(Session::get('cart', []));
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Kouta voucher habis',
            'data' => null,
        ]);
    }

    /**
     * Test when the voucher is valid.
     */
    public function testCheckVoucherValid()
    {
        $booking = $this->logicPaymentPage();
        $voucher = Voucher::where('code', 'DISCOUNT10')->first();

        $response = $this->post('/api/voucher/' . $voucher->code );

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Voucher valid',
            'data' => [
                'voucher' => (new VoucherResource($voucher))->resolve(),
                'discount' => 10000,
                'total_price' => 90000,
            ],
        ]);
        dump($response->getContent());
        dump(Session::get('cart', []));
    }

    /**
     * Test when the voucher is valid with a fixed discount.
     */
    public function testCheckVoucherValidFixedDiscount()
    {
        $booking = $this->logicPaymentPage();
        $voucher = Voucher::where('code', 'FLAT50')->first();
        $cart = Session::get('cart', []);
        $cart['total_price'] = 200000;
        Session::put('cart', $cart);

        $response = $this->post('/api/voucher/' . $voucher->code );
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Voucher valid',
            'data' => [
                'voucher' => (new VoucherResource($voucher))->resolve(),
                'discount' => 50000,
                'total_price' => 150000,
            ],
        ]);
    }

    /**
     * Test when the voucher is not valid due to minimum price.
     */
    public function testCheckVoucherNotValidMinimumPrice()
    {
        $booking = $this->logicPaymentPage();
        $voucher = Voucher::where('code', 'FLAT50')->first();

        $response = $this->post('/api/voucher/' . $voucher->code );
        dump($response->getContent());
        dump(Session::get('cart', []));
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Minimal transaksi tidak mencukupi',
            'data' => null,
        ]);
    }
}
