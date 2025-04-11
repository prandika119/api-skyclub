<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class CancelExpiredBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:cancel-expired-bookings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel all pending bookings that have expired';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredBookings = Booking::where('status', 'pending')
            ->where('expired_at', '<=', now())
            ->get();

        foreach ($expiredBookings as $booking) {
            $booking->update(['status' => 'canceled']);
        }
        $this->info('Expired bookings canceled: ' . $expiredBookings->count());
    }
}
