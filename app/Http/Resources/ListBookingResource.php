<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListBookingResource extends JsonResource
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
            'date' => $this->date,
            'session' => $this->session,
            'price' => $this->price,
            'status' => $this->status_request ?? $this->booking->status,
            'order_date' => $this->booking->order_date,
            'booking_id' => $this->booking_id,
            'field' => new FieldSimpleResource($this->field),
            'user' => new UserResource($this->booking->rentedBy),
            'has_sparing' => $this->sparing ? true : false,
            'review' => [
                'rating' => $this->booking->review->rating ?? null,
                'comment' => $this->booking->review->comment ?? null,
            ],
        ];
    }
}
