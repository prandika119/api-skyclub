<?php

namespace App\Events;

use App\Models\SparingRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AcceptedSparingEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public SparingRequest $sparingRequest;
    /**
     * Create a new event instance.
     */
    public function __construct($sparingRequest)
    {
        $this->sparingRequest = $sparingRequest;
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
