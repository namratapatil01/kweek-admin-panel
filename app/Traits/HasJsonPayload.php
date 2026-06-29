<?php

namespace App\Traits;

trait HasJsonPayload
{
    public function mergePayload(array $attributes): void
    {
        $payload = $this->payload ?? [];
        if (! is_array($payload)) {
            $payload = [];
        }

        $this->payload = array_merge($payload, $attributes);
    }

    public function getPayloadAttribute($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
