<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\RequestCancelScheduleNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class NotifyRequestCancelSchedule
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
        Notification::send($admins, new RequestCancelScheduleNotification($event->listBooking));
    }
}
