<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\SuccessBookingNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class NotifySuccessBooking
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
        $admin = User::where('role', 'admin')->get();
        $event->user->notify(new SuccessBookingNotification($event->booking));
//        Notification::send($event->user, new SuccessBookingNotification($event->booking));
        Notification::send($admin, new SuccessBookingNotification($event->booking));
    }
}
