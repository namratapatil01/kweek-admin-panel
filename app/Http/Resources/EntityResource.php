<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        if (method_exists($this->resource, 'toDocumentArray')) {
            return $this->resource->toDocumentArray();
        }

        if (isset($data['payload']) && is_array($data['payload'])) {
            $data = array_merge($data, $data['payload']);
            unset($data['payload']);
        }

        return $data;
    }
}
