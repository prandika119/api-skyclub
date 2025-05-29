<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherResource extends JsonResource
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
            'expired_date' => $this->expire_date,
            'code' => $this->code,
            'quota' => $this->quota,
            'discount_price' => $this->discount_price,
            'discount_precentage' => $this->discount_precentage,
            'max_discount' => $this->max_discount,
            'min_price' => $this->min_price,
        ];
    }
}
