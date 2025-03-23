<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FieldResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'weekday_price' => $this->weekday_price,
            'weekend_price' => $this->weekend_price,
            'photos' => FieldImageResource::collection($this->photos),
            'facilities' => FacilityResource::collection($this->facilities),
        ];
    }
}
