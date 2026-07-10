<?php

namespace App\Http\Resources\Provider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderLanguageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->resource['slug'] ?? $this->resource['code'] ?? null,
            'title' => $this->resource['title'] ?? $this->resource['name'] ?? null,
            'isActive' => (bool) ($this->resource['isActive'] ?? $this->resource['isactive'] ?? true),
        ];
    }
}
