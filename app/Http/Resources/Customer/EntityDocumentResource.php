<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntityDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (method_exists($this->resource, 'toDocumentArray')) {
            return $this->resource->toDocumentArray();
        }

        return parent::toArray($request);
    }
}
