<?php

namespace App\Providers;

use App\Events\AcceptedCancelScheduleEvent;
use App\Events\AcceptedRescheduleEvent;
use App\Events\AcceptedSparingEvent;
use App\Events\RejectedCancelScheduleEvent;
use App\Events\RejectedRescheduleEvent;
use App\Events\RejectedSparingEvent;
use App\Events\RequestCancelScheduleEvent;
use App\Events\RequestRescheduleEvent;
use App\Events\RequestSparingEvent;
use App\Events\SuccessBookingEvent;
use App\Listeners\NotifyAcceptCancelSchedule;
use App\Listeners\NotifyAcceptReschedule;
use App\Listeners\NotifyAcceptSparing;
use App\Listeners\NotifyRejectCancelSchedule;
use App\Listeners\NotifyRejectReschedule;
use App\Listeners\NotifyRejectSparing;
use App\Listeners\NotifyRequestCancelSchedule;
use App\Listeners\NotifyRequestReschedule;
use App\Listeners\NotifyRequestSparing;
use App\Listeners\NotifySuccessBooking;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Mockery\Matcher\Not;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        SuccessBookingEvent::class => [
            NotifySuccessBooking::class,
        ],
        RequestRescheduleEvent::class => [
            NotifyRequestReschedule::class,
        ],
        AcceptedRescheduleEvent::class => [
            NotifyAcceptReschedule::class
        ],
        RejectedRescheduleEvent::class => [
            NotifyRejectReschedule::class
        ],
        RequestCancelScheduleEvent::class => [
            NotifyRequestCancelSchedule::class
        ],
        AcceptedCancelScheduleEvent::class => [
            NotifyAcceptCancelSchedule::class
        ],
        RejectedCancelScheduleEvent::class => [
            NotifyRejectCancelSchedule::class
        ],
        RequestSparingEvent::class => [
            NotifyRequestSparing::class
        ],
        AcceptedSparingEvent::class => [
            NotifyAcceptSparing::class
        ],
        RejectedSparingEvent::class => [
            NotifyRejectSparing::class
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
