<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListBookingSparingResource extends JsonResource
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
            'status' => $this->sparing->status,
            'field' => new FieldSimpleResource($this->field),
            'user' => new UserResource($this->sparing->createdBy),
            'sparing_request' => $this->sparing->sparingRequest,
        ];
    }
}
