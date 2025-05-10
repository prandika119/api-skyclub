<?php

namespace App\Listeners;

use App\Notifications\AcceptRescheduleNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAcceptReschedule
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
        $listBooking->booking->rentedBy->notify(new AcceptRescheduleNotification($listBooking));
    }
}
