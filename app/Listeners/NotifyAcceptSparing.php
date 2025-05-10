<?php

namespace App\Listeners;

use App\Notifications\AcceptSparingNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAcceptSparing
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
        $sparingRequest->user->notify(new AcceptSparingNotification($sparingRequest));

    }
}
