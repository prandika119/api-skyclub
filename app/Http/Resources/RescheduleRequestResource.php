<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RescheduleRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'new_schedule' => new ListBookingResource($this->newListBooking),
            'old_schedule' => new ListBookingResource($this->oldListBooking),
            'user' => new UserResource($this->user),
            'created_at' => $this->created_at
        ];
    }
}
