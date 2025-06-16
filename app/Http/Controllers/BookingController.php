<?php

namespace App\Http\Controllers;

use App\Events\SuccessBookingEvent;
use App\Http\Requests\PaymentRequest;
use App\Http\Requests\SchedulesCartRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\CartResource;
use App\Http\Resources\ListBookingResource;
use App\Models\Booking;
use App\Models\ListBooking;
use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class BookingController extends Controller
{
    /**
     * Get All Booking In Admin
     *
     *
     */
    public function index()
    {
        $bookings = ListBooking::whereHas('booking', function ($query){
            $query->where('status', '!=', 'pending');
        })->latest()->get();
        Log::info('bookings', [$bookings]);
        return response([
            'message' => 'Success',
            'data' => ListBookingResource::collection($bookings)
        ]);
    }

    /**
     * Store Booking (Not Yet Payment)
     *
     * Store a Booking & Navigate Payment Page
     */
    public function store(SchedulesCartRequest $request)
    {
        $schedules = $request->validated();

        Log::info('schedules', $schedules);
        if (!isset($schedules['schedules'])) {
            throw new \Exception('Key schedules tidak ditemukan di data yang divalidasi');
        }

//        Log::info('schedules', [$schedules['schedules'][0]['field_id']]);
        $user = auth()->user();
        DB::beginTransaction();
        try {
            if ($user->role != 'admin'){
                $booking = Booking::create(
                    [
                        'order_date' => now(),
                        'rented_by' => $user->id,
                        'expired_at' => now()->addMinutes(5),
                    ]
                );
            } else {
                return response([
                    'message' => 'Bad Request',
                    'errors' => 'Admin tidak bisa booking di sini'
                ], 400);
            }

            foreach ($schedules['schedules'] as $schedule){
                ListBooking::create([
                    'field_id' => $schedule['field_id'],
                    'booking_id' => $booking->id,
                    'date' => $schedule['schedule_date'],
                    'session' => $schedule['schedule_time'],
                    'price' => $schedule['price'],
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => 'Internal Server Error',
                'errors' => $e->getMessage()
            ], 500);
        }


        $list_booking = ListBooking::where('booking_id', $booking->id)->get();
        Log::info('list_booking', [$list_booking]);

        return response([
            'message' => 'Booking Created',
            'data' => [
                'booking' => $booking,
                'cart' => CartResource::collection($list_booking),
            ]
        ]);
    }

    /**
     * Booking For Offline User
     *
     * Store a Booking For Offline User & Navigate Payment Page
     */
    public function storeOffline()
    {
        $user = auth()->user();
        $cart = Session::get('cart', []);

        return response([
            'message' => 'Booking Created',
            'data' => [
                'cart' => $cart
            ]
        ]);
    }

    /**
     * Select User for Booking
     *
     * Select User Offline for Booking
     */
    public function selectUser(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);
        $user = User::where('id', $data['user_id'])->first();
        $booking = Booking::create(
            [
                'order_date' => now(),
                'rented_by' => $user->id,
                'expired_at' => now()->addMinutes(5),
            ]
        );
        return response([
            'message' => 'User Selected',
            'data' => [
                'user' => $user,
                'booking' => $booking
            ]
        ]);
    }


    /**
     * Payment
     *
     * Payment for Booking
     */
    public function payment(PaymentRequest $request)
    {
        $data = $request->validated();
        Log::info('payload', [$data]);
//        $cart = Session::get('cart', []);
        $user = auth()->user();
        $wallet = $user->wallet->balance;
        $booking = Booking::where('id', $data['booking_id'])->firstOrFail();
        Log::info('booking', [$booking]);

//        $schedules_cart = collect($cart['schedules']);
        $schedules_cart = $booking->listBooking;
        Log::info('schedules_cart', [$schedules_cart]);

        $schedule_dates = $schedules_cart->pluck('session')->unique()->values()->toArray();
        Log::info('schedules_dates', [$schedule_dates]);
//        $schedule_dates = $schedules_cart->pluck('schedule_date')->unique()->values()->toArray();

        $schedules_booked = ListBooking::whereIn('date', $schedule_dates)->whereHas('booking',function ($query){
            $query->where('status', '!=', 'pending');
        })->get();
        Log::info('schedules_booked', [$schedules_booked]);

//        $voucher = isset($cart['voucher']['id'])? Voucher::find($cart['voucher']['id']) : null;
        $voucher = $booking->voucher;
        Log::info('voucher', [$voucher]);

        $totalPrice = $data['total_price'];

        $conflict = false;

        // Check Time to Payment
        if ($booking->expired_at < now()){
            return response([
                'message' => 'Bad Request',
                'errors' => 'Waktu Pembayaran Sudah Habis'
            ], 400);
        }

        // Check Wallet Balance
        if ($wallet < $totalPrice && $user->role != 'admin'){
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
//            foreach ($schedules_cart as $schedule) {
//                ListBooking::create([
//                    'date' => Carbon::parse($schedule['schedule_date']),
//                    'session' => $schedule['schedule_time'],
//                    'price' => $schedule['price'],
//                    'field_id' => $schedule['field_id'],
//                    'booking_id' => $data['booking_id']
//                ]);
//            }
            if ($user->role != 'admin'){
                // Create Recent Transaction
                $user->recentTransactions()->create([
                    'user_id' => $user->id,
                    'wallet_id' => $user->wallet->id,
                    'transaction_type' => 'booking',
                    'amount' => $totalPrice,
                    'bank_ewallet' => null,
                    'number' => null,
                ]);
                $user->wallet()->update([
                    'balance' => $wallet - $totalPrice
                ]);
            }

            // Check Voucher
            if ($voucher){
                $booking->update([
                    'status' => 'accepted',
                    'voucher_id' => $voucher->id,
                ]);
                $voucher->update([
                    'quota' => $voucher->quota - 1,
                ]);
            } else {
                $booking->update([
                    'status' => 'accepted',
                ]);
            }

            DB::commit();

//            $rentedBy = User::where('id', $booking->rented_by)->first();
            // Send Notification
            event(new SuccessBookingEvent($user, $booking));

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
