<?php

namespace Tests\Unit;

use App\Http\Resources\VoucherResource;
use App\Models\User;
use App\Models\Voucher;
use Database\Seeders\VoucherSeeder;
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
        $this->seed(VoucherSeeder::class);
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
        $this->seed(VoucherSeeder::class);
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
        $this->seed(VoucherSeeder::class);
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
        $this->seed(VoucherSeeder::class);
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
        $this->seed(VoucherSeeder::class);
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
        $this->seed(VoucherSeeder::class);
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

    /**
     * Test the store method.
     */
    public function testStoreVoucher()
    {
        $data = [
            'expire_date' => now()->addDays(10)->toDateString(),
            'code' => 'NEWVOUCHER',
            'quota' => 100,
            'discount_price' => 5000,
            'min_price' => 20000,
        ];
        $admin = $this->AuthAdmin();
        $response = $this->post('/api/vouchers', $data);
        dump($response->getContent());
        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Voucher created successfully',
            'data' => null,
        ]);

        $this->assertDatabaseHas('vouchers', ['code' => 'NEWVOUCHER']);
    }

    /**
     * Test the show method.
     */
    public function testShowVoucher()
    {
        $this->AuthAdmin();
        $this->seed(VoucherSeeder::class);
        $voucher = Voucher::where('code', 'DISCOUNT10')->first();
        $response = $this->get('/api/vouchers/' . $voucher->id);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Success',
            'data' => [
                'id' => $voucher->id,
                'code' => $voucher->code,
            ],
        ]);
    }

    /**
     * Test the update method.
     */
    public function testUpdateVoucher()
    {
        $this->AuthAdmin();
        $this->seed(VoucherSeeder::class);
        $voucher = Voucher::where('code', 'DISCOUNT10')->first();

        $data = [
            'expire_date' => now()->addDays(15),
            'code' => 'UPDATEDVOUCHER',
            'quota' => 50,
            'discount_price' => 10000,
            'discount_percentage' => 0,
            'min_price' => 30000,
        ];
        $response = $this->put('/api/vouchers/'. $voucher->id, $data);
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Voucher updated successfully',
            'data' => null,
        ]);
    }

    /**
     * Test the destroy method.
     */
    public function testDestroyVoucher()
    {
        $this->AuthAdmin();
        $this->seed(VoucherSeeder::class);
//        $voucher = Voucher::where('code', 'DNT1')->first();
        $voucher = Voucher::where('code', 'DISCOUNT10')->first();
        dump($voucher);

        $response = $this->delete('/api/vouchers/'. $voucher->id);
//        dump($response);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Voucher deleted successfully',
            'data' => null,
        ]);

        $this->assertDatabaseMissing('vouchers', ['id' => $voucher->id]);
    }
}
