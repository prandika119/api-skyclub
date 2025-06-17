<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVoucherRequest;
use App\Http\Requests\UpdateVoucherRequest;
use App\Http\Resources\VoucherResource;
use App\Models\Booking;
use App\Models\ListBooking;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class VoucherController extends Controller
{
    /**
     * Check voucher
     *
     * Check voucher validity and apply discount
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function checkVoucher(Request $request)
    {
//        $cart = Session::get('cart', []);
        $code = $request->route('code');
        $booking = Booking::where('id', $request->route('booking'))->first();
        $voucher = Voucher::where('code', $code)->first();
        Log::info('voucher', [$voucher]);
        Log::info('booking', [$booking]);
        $discountAmount = 0;
        $totalPrice = $booking->listBooking->sum('price');

        if (!$voucher) {
            $booking->update([
                'voucher_id' => null,
            ]);

            return response([
                'message' => 'Voucher tidak ditemukan',
                'data' => null
            ], 404);
        }
        if ($voucher->isExpired()) {
            return response([
                'message' => 'Voucher sudah kadaluarsa',
                'data' => null
            ], 400);
        }

        if ($voucher->quota <= 0) {
            return response([
                'message' => 'Kouta voucher habis',
                'data' => null
            ], 400);
        }

        if ($totalPrice < $voucher->min_price) {
            return response([
                'message' => 'Minimal transaksi tidak mencukupi',
                'data' => null
            ], 400);
        }


        if ($voucher->discount_percentage > 0) {
            // Hitung diskon persentase
            $discountAmount = $totalPrice * ($voucher->discount_percentage / 100);

            // Batasi maksimal diskon jika ada max_discount
            if ($voucher->max_discount > 0) {
                $discountAmount = min($discountAmount, $voucher->max_discount);
            }
        } elseif ($voucher->discount_price > 0) {
            // Diskon dalam nominal fix
            $discountAmount = $voucher->discount_price;
        }

        // Pastikan diskon tidak melebihi total harga
        $discountAmount = min($discountAmount, $totalPrice);

        // add voucher to booking
        $booking->update([
            'voucher_id' => $voucher->id,
        ]);

        // update voucher quota
//        $voucher->update([
//            'quota' => $voucher->quota - 1,
//        ]);

        // update total price
        $totalPrice -= $discountAmount;

        return response([
            'message' => 'Voucher valid',
            'data' => [
                'voucher' => new VoucherResource($voucher),
                'discount' => $discountAmount,
                'total_price' => $totalPrice
            ]
        ], 200);
    }

    /**
     * Get All of Vouchers
     *
     * Display a listing of the resource.
     */
    public function index()
    {
        $vouchers = Voucher::orderBy('created_at', 'desc')->get();
        return response([
            'message' => 'Successs',
            'data' => VoucherResource::collection($vouchers)
        ], 200);
    }

    /**
     * Create a new voucher
     *
     * Store a newly created resource in storage.
     */
    public function store(StoreVoucherRequest $request)
    {
        $data = $request->validated();
        if (isset($data['discount_price']) && isset($data['discount_precentage'])){
            return response([
                'message' => 'Hanya satu diskon yang boleh diisi',
                'data' => null
            ], 400);
        }
        if (Carbon::parse($data['expire_date'])->isPast()) {
            return response([
                'message' => 'Tanggal kadaluarsa tidak boleh kurang dari hari ini',
                'data' => null
            ], 400);
        }

        Voucher::create($data);
        return response([
            'message' => 'Voucher created successfully',
            'data' => null
        ], 201);
    }

    /**
     * Get Voucher by id
     *
     * Display the specified resource.
     */
    public function show(Voucher $voucher)
    {
        return response([
            'message' => 'Success',
            'data' => new VoucherResource($voucher)
        ], 200);
    }


    /**
     * Update Voucher
     *
     * Update the specified resource in storage.
     */
    public function update(UpdateVoucherRequest $request, Voucher $voucher)
    {
        $data = $request->validated();
        if (isset($data['discount_price']) && isset($data['discount_percentage'])) {
            return response([
                'message' => 'Hanya satu diskon yang boleh diisi',
                'data' => null
            ], 400);
        }
        if (Carbon::parse($data['expire_date'])->isPast()) {
            return response([
                'message' => 'Tanggal kadaluarsa tidak boleh kurang dari hari ini',
                'data' => null
            ], 400);
        }

        $voucher->update([$data]);
        return response([
            'message' => 'Voucher updated successfully',
            'data' => null
        ], 200);
    }

    /**
     * Delete Voucher
     *
     * Remove the specified resource from storage.
     */
    public function destroy(Voucher $voucher)
    {
        // Cek apakah voucher ada
        dump('yuyuyu');
        dump($voucher);
        if (!$voucher) {
            return response([
                'message' => 'Voucher tidak ditemukan',
                'data' => null
            ], 404);
        }
        // Pastikan voucher tidak digunakan dalam transaksi
        if ($voucher->bookings()->exists()) {
            return response([
                'message' => 'Voucher tidak dapat dihapus karena sudah digunakan dalam transaksi',
                'data' => null
            ], 400);
        }
        // Hapus voucher
        try {
            $voucher->delete();
        } catch (\Exception $e) {
            return response([
                'message' => 'Gagal menghapus voucher',
                'data' => null
            ], 500);
        }
        return response([
            'message' => 'Voucher deleted successfully',
            'data' => null
        ], 200);
    }
}
