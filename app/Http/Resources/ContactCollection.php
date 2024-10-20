<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ContactCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            "data" => ContactResource::collection(
                $this->collection
            )
        ];
    }
}
