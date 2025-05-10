<?php

namespace App\Events;

use App\Models\ListBooking;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestCancelScheduleEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public ListBooking $listBooking;
    /**
     * Create a new event instance.
     */
    public function __construct(ListBooking $listBooking)
    {
        $this->listBooking = $listBooking;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
