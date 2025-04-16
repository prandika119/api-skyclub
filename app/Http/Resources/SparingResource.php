<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SparingResource extends JsonResource
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
                'list_booking' => new ListBookingResource($this->listBooking),
                'created_by' => new UserResource($this->createdBy),
                'description' => $this->description,
                'status' => $this->status,
            ];
    }
}
