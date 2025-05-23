<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\RequestRescheduleNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class NotifyRequestReschedule
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
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new RequestRescheduleNotification($event->listBooking));
    }
}
