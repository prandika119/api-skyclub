<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecentTransactionResource extends JsonResource
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
            'transaction_type' => $this->transaction_type,
            'amount' => $this->amount,
            'bank_ewallet' => $this->bank_ewallet,
            'number' => $this->number,
            'created_at' => $this->created_at,
            'recipient' => $this->whenNotNull(new UserResource($this->recipient)),
        ];
    }
}
