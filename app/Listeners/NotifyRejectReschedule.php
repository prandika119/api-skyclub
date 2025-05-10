<?php

namespace App\Listeners;

use App\Notifications\RejectRescheduleNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyRejectReschedule
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $listBooking = $event->listBooking;
        $listBooking->booking->rentedBy->notify(new RejectRescheduleNotification($listBooking));
    }
}
