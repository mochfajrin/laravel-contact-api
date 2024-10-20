<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "postal_code" => $this->postal_code,
            "street" => $this->street,
            "city" => $this->city,
            "province" => $this->province,
            "country" => $this->country,
        ];
    }
}
