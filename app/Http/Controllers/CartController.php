<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchedulesCartRequest;
use App\Http\Requests\StoreCartRequest;
use App\Http\Resources\CartResource;
use App\Http\Resources\ListBookingResource;
use App\Models\Booking;
use App\Models\ListBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;


class CartController extends Controller
{
    /**
     * Get All Cart
     *
     * Display a list cart
     */
    public function index(Booking $booking)
    {
        $list_booking = ListBooking::where('booking_id', $booking->id)->whereHas('booking', function ($query){
            $query->where('status', 'pending');
        })->get();
        $total_price = $list_booking->sum('price');
        $discountAmount = 0;
        $voucher= $booking->voucher;
        Log::info('voucher', [$voucher]);
        if ($voucher){
            if ($voucher->discount_percentage > 0) {
                // Hitung diskon percentage
                $discountAmount = $total_price * ($voucher->discount_percentage / 100);

                // Batasi maksimal diskon jika ada max_discount
                if ($voucher->max_discount > 0) {
                    $discountAmount = min($discountAmount, $voucher->max_discount);
                }
            } elseif ($voucher->discount_price > 0) {
                // Diskon dalam nominal fix
                $discountAmount = $voucher->discount_price;
            }

            // Pastikan diskon tidak melebihi total harga
            $discountAmount = min($discountAmount, $total_price);
        }
        $total_price -= $discountAmount;

        return response([
            'message' => 'Success',
            'data' => [
                'cart' =>  CartResource::collection($list_booking),
                'sub_total' => $list_booking->sum('price'),
                'total_price' => $total_price,
                'code_voucher' => $voucher ? $voucher->code : null,
                'discount' =>$discountAmount ?? 0,
            ]
        ], 200);
    }


    public function saveAllCart(SchedulesCartRequest $request, Booking $booking)
    {
        $schedules = $request->validated();
        foreach ($schedules as $schedule){
            ListBooking::create([
                'field_id' => $schedule['field_id'],
                'booking_id' => $booking->id,
                'date' => $schedule['schedule_date'],
                'session' => $schedule['schedule_time'],
                'price' => $schedule['price'],
            ]);
        }
        return response([
            'message' => 'All schedules saved successfully',
        ], 201);
    }

    /**
     * Add schedule to cart
     *
     * Store a schedule cart
     */
    public function store(StoreCartRequest $request)
    {
        $data = $request->validated();
        $cart = Session::get('cart', []);
        $schedules_cart = $cart['schedules'] ?? [];

        // Cek duplikat berdasarkan kombinasi field_id, schedule_date, dan schedule_time
        $duplicate = collect($schedules_cart)->contains(function ($item) use ($data) {
            return $item['field_id'] == $data['field_id'] &&
                $item['schedule_date'] == $data['schedule_date'] &&
                $item['schedule_time'] == $data['schedule_time'];
        });

        if ($duplicate) {
            return response([
                'message' => 'Bad Request',
                'errors' => 'Jadwal tersebut sudah ada dalam keranjang'
            ], 400);
        }

        $cart['schedules'][] = $data;
        $cart['total_price'] = array_sum(array_column($cart['schedules'], 'price'));
        Session::put('cart', $cart);
        return response([
            'message' => 'Cart added successfully',
            'data' => $cart
        ], 201);
    }


    /**
     * Delete schedule from cart
     *
     * Remove schedule from cart
     */
    public function destroy(string $id)
    {
        $cart = Session::get('cart', []);
        if (!isset($cart['schedules'][$id])) {
            return response(['message' => 'Bad Request', 'errors' => 'Item not found'], 404);
        }
        unset($cart['schedules'][$id]);
        $cart['total_price'] = array_sum(array_column($cart['schedules'], 'price'));
        Session::put('cart', $cart);
        return response([
            'message' => 'Cart deleted successfully',
            'data' => $cart
        ], 200);
    }
}
