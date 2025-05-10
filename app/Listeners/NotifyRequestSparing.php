<?php

namespace App\Listeners;

use App\Notifications\RequestSparingNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyRequestSparing
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
        $sparingRequest->user->notify(new RequestSparingNotification($sparingRequest));
    }
}
