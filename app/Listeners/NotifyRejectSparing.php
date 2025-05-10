<?php

namespace App\Listeners;

use App\Notifications\RejectSparingNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyRejectSparing
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
        $sparingRequest = $event->sparingRequest;
        $sparingRequest->user->notify(new RejectSparingNotification($sparingRequest));
    }
}
