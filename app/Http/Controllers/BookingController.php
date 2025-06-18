<?php

namespace App\Http\Controllers;

use App\Events\SuccessBookingEvent;
use App\Http\Requests\PaymentRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\SchedulesCartRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
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
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * Get All Booking In Admin
     *
     *
     */
    public function index()
    {
        $bookings = Booking::where('status', '!=', 'pending')->has('listBooking')->latest()->get();
        return response([
            'message' => 'Success',
            'data' => BookingResource::collection($bookings)
        ]);
    }

    /**
     * Get Stats Booking
     *
     *
     */
    public function getBookingStats()
    {
        // Asumsi: Hanya booking dengan status 'Completed' yang dihitung sebagai pendapatan.
        // Ganti 'Completed' dengan status yang sesuai di sistem Anda.
        $statusSelesai = 'accepted';

        // --- Perhitungan Statistik ---

        // 1. Hari Ini
        $todayQuery = Booking::whereDate('order_date', today());
        $statsToday = [
            'booking_count' => $todayQuery->count(),
            'total_revenue' => (float) $this->calculateRevenue($todayQuery, $statusSelesai)
        ];

        // 2. Minggu Ini (dari Senin s/d Minggu)
        $thisWeekQuery = Booking::whereBetween('order_date', [
            now()->startOfWeek(Carbon::MONDAY),
            now()->endOfWeek(Carbon::SUNDAY)
        ]);
        $statsThisWeek = [
            'booking_count' => $thisWeekQuery->count(),
            'total_revenue' => (float) $this->calculateRevenue($thisWeekQuery, $statusSelesai)
        ];

        // 3. Bulan Ini
        $thisMonthQuery = Booking::whereMonth('order_date', now()->month)
            ->whereYear('order_date', now()->year);
        $statsThisMonth = [
            'booking_count' => $thisMonthQuery->count(),
            'total_revenue' => (float) $this->calculateRevenue($thisMonthQuery, $statusSelesai)
        ];

        // 4. Tahun Ini
        $thisYearQuery = Booking::whereYear('order_date', now()->year);
        $statsThisYear = [
            'booking_count' => $thisYearQuery->count(),
            'total_revenue' => (float) $this->calculateRevenue($thisYearQuery, $statusSelesai)
        ];

        // --- Gabungkan Semua Data ---
        $data = [
            'today' => $statsToday,
            'this_week' => $statsThisWeek,
            'this_month' => $statsThisMonth,
            'this_year' => $statsThisYear,
        ];

        return response()->json([
            'message' => 'Ringkasan statistik berhasil diambil',
            'data' => $data,
        ]);
    }

    /**
     * Calculate Revenue
     *
     * Calculate total revenue from bookings
     */
    private function calculateRevenue($query, string $status): float
    {
        // Kita perlu clone query agar tidak mempengaruhi perhitungan count sebelumnya
        return $query->clone()
            ->join('list_bookings', 'bookings.id', '=', 'list_bookings.booking_id')
            ->where('bookings.status', $status)
            ->sum('list_bookings.price');
    }

    /**
     * Get Time Series Statistics
     */
    public function getTimeSeriesStats(Request $request){
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'days' => 'integer|min:1|max:365', // Validasi 'days' agar berupa angka antara 1-365
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Ambil parameter 'days' dengan nilai default 7
        $days = $request->input('days', 7);

        // 2. Tentukan Rentang Tanggal
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays($days - 1);

        // 3. Ambil Data Booking dari Database, dikelompokkan per hari
        // Kita menggunakan Query Builder untuk performa dan fleksibilitas
        $bookings = DB::table('bookings')
            ->select(DB::raw('DATE(order_date) as date'), DB::raw('count(*) as count'))
            ->whereBetween('order_date', [$startDate, $endDate])
            // Tambahkan filter status jika perlu (misal: hanya hitung yang 'Completed')
            // ->where('status', 'Completed')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            // Ubah hasil query menjadi array asosiatif [ 'YYYY-MM-DD' => count ] agar mudah diakses
            ->keyBy('date')
            ->map(function ($item) {
                return $item->count;
            });

        // 4. Siapkan rentang tanggal lengkap (termasuk hari dengan 0 booking)
        $period = CarbonPeriod::create($startDate, $endDate);
        $results = [];

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $results[] = [
                'date' => $formattedDate,
                // Ambil jumlah booking dari hasil query, jika tidak ada, defaultnya 0
                'count' => $bookings->get($formattedDate, 0),
            ];
        }

        // 5. Kembalikan Respons
        return response()->json($results);
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
            $booking = Booking::create(
                [
                    'order_date' => now(),
                    'rented_by' => $user->id,
                    'expired_at' => now()->addMinutes(5),
                ]
            );

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
     * Create Offline User for Booking
     *
     * Create User and Select to Booking
     */
    public function createUser(RegisterRequest $request, Booking $booking)
    {
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);
        $user->wallet()->create(['balance' => 0]);
        $booking->update([
            'rented_by' => $user->id
        ]);
        return response([
            'message' => 'User created and selected to booking',
            'data' => [
                'user' => $user,
                'booking' => $booking
            ]
        ], 201);
    }

    /**
     * Search User
     *
     * Search User for Booking
     */
    public function searchUser(Request $request)
    {
        $query = $request->input('query'); // Parameter pencarian dari request

        if (empty($query)) {
            return response()->json([
                'message' => 'Please provide a search query (name, phone number, or email).',
                'data' => []
            ], 400); // Bad Request
        }

        $users = User::where(function($q) use ($query) {
            $q->where('name', 'like', '%' . $query . '%')
                ->orWhere('no_telp', 'like', '%' . $query . '%')
                ->orWhere('email', 'like', '%' . $query . '%');
        })->get();

        if ($users->isEmpty()) {
            return response()->json([
                'message' => 'No users found matching your query.',
                'data' => []
            ], 404); // Not Found
        }

        return response()->json([
            'message' => 'Users found successfully.',
            'data' => $users
        ], 200); // OK
    }

    /**
     * Select User for Booking
     *
     * Select User Offline for Booking
     */
    public function selectUser(Booking $booking, Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);
        $user = User::where('id', $data['user_id'])->first();
        $booking->update([
                'rented_by' => $user->id
        ]);

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
