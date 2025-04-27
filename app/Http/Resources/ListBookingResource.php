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
            'status' => $this->booking->status,
            'order_date' => $this->booking->order_date,
            'field' => new FieldSimpleResource($this->field),
            'review' => [
                'rating' => $this->booking->review->rating ?? null,
                'comment' => $this->booking->review->comment ?? null,
            ]
//            'review' => $this->booking->whenLoaded('review', function (){
//                return [
//                    'id' => $this->review->id,
//                    'rating' => $this->review->rating,
//                    'comment' => $this->review->comment,
//                ];
//            })

        ];
    }
}
