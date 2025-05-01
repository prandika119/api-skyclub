<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CancelRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return
            [
                'id' => $this->id,
                'reason' => $this->reason,
                'reply' => $this->reply,
                'created_at' => $this->created_at,
                'booking' => new ListBookingResource($this->listBooking),
                'user' => new UserResource($this->user),
            ];
    }
}
