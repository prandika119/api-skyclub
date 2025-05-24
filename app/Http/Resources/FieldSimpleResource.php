<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FieldSimpleResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'photos' => FieldImageResource::collection($this->photos),
            'review' => [
                'average' => $this->reviews->avg('rating'),
                'count' => $this->reviews()->count()
            ]
        ];
    }
}
