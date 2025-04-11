<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\ListBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookingRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();
        $booking = Booking::create(
            [
                'order_date' => now(),
                'rented_by' => $user->id,
                'expired_at' => now()->addMinutes(5),
            ]
        );
        $cart = Session::get('cart', []);

        return response([
            'message' => 'Booking Created',
            'data' => [
                'booking' => $booking,
                'cart' => $cart
            ]
        ]);
    }

    public function payment(PaymentRequest $request)
    {
        $data = $request->validated();
        $cart = Session::get('cart', []);
        $user = auth()->user();
        $wallet = $user->wallet->balance;
        $booking = Booking::where('id', $data['booking_id'])->firstOrFail();
        $schedules_cart = collect($cart['schedules']);
        $schedule_dates = $schedules_cart->pluck('schedule_date')->unique()->values()->toArray();
        $schedules_booked = ListBooking::whereIn('date', $schedule_dates)->get();
        $conflict = false;

        //dump($schedule_dates);
        //dump($schedules_booked);
        // Check Time to Payment
        if ($booking->expired_at < now()){
            return response([
                'message' => 'Bad Request',
                'errors' => 'Waktu Pembayaran Sudah Habis'
            ], 400);
        }

        // Check Wallet Balance
        if ($wallet < $cart['total_price']){
            return response([
                'message' => 'Bad Request',
                'errors' => 'Saldo tidak mencukupi'
            ], 400);
        }

        // Checking Schedules in Database (Can't Booking if Other User Booked same schedule)
        foreach ($schedules_cart as $schedule){
            foreach ($schedules_booked as $booked){
                if ($schedule['schedule_date'] == $booked['date'] && $schedule['schedule_time'] == $booked['session']){
                    $conflict = true;
                    break 2; // keluar dari kedua loop
                }
            }
        }

        if ($conflict) {
            return response([
                'message' => 'Bad Request',
                'errors' => 'Jadwal sudah dibooking oleh orang lain'
            ], 400);
        }

        // All Schedules save, continue to DB Transaction
        DB::beginTransaction();
        try {
            foreach ($schedules_cart as $schedule) {
                ListBooking::create([
                    'date' => Carbon::parse($schedule['schedule_date']),
                    'session' => $schedule['schedule_time'],
                    'price' => $schedule['price'],
                    'field_id' => $schedule['field_id'],
                    'booking_id' => $data['booking_id']
                ]);
            }
            $user->wallet()->update([
                'balance' => $wallet - $cart['total_price']
            ]);
            $booking->update([
                'status' => 'accepted'
            ]);

            DB::commit();

            return response([
                'message' => 'Jadwal berhasil dibooking'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => 'Internal Server Error',
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
