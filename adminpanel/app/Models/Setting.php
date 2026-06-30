<?php

namespace App\Models;

use App\Models\Concerns\KweekModel;

class Setting extends KweekModel
{
    protected $table = 'settings';

    protected $casts = [
        'value' => 'array',
    ];

    public function getPayloadAttribute($value): array
    {
        return $this->value ?? [];
    }

    public function toDocumentArray(): array
    {
        return array_merge(['id' => $this->id], $this->value ?? []);
    }
}
