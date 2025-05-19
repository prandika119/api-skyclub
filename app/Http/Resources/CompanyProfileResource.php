<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyProfileResource extends JsonResource
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
            'address' => $this->address,
            'no_telp' => $this->no_telp,
            'email' => $this->email,
            'description' => $this->description,
            'payment' => $this->payment,
            'logo' => $this->logo,
            'banner' => $this->banner,
            'slider_1' => $this->slider_1,
            'slider_2' => $this->slider_2,
            'slider_3' => $this->slider_3,
        ];
    }
}
