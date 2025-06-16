<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'order_date' => $this->order_date,
            'status' => $this->status,
            'user' => new UserResource($this->rentedBy),
            'list_bookings' => $this->listBooking
        ];
    }
}
