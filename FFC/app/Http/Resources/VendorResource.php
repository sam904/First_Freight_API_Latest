<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'vendorId' => $this->id,
            'companyName' => $this->company_name,
            'contact' => $this->contact_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'status' => $this->status,
            'city' => $this->city,
            'country' => $this->country->name,
            'state' => $this->state->name,
        ];
    }
}
