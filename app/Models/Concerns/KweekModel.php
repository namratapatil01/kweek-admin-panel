<?php

namespace App\Models\Concerns;

use App\Traits\HasJsonPayload;
use App\Traits\HasStringPrimaryKey;
use Illuminate\Database\Eloquent\Model;

abstract class KweekModel extends Model
{
    use HasStringPrimaryKey;
    use HasJsonPayload;

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'createdAt' => 'datetime',
    ];

    public function toDocumentArray(): array
    {
        $data = array_merge($this->attributesToArray(), $this->payload ?? []);
        unset($data['payload'], $data['created_at'], $data['updated_at']);

        return array_filter($data, static fn ($value) => $value !== null);
    }
}
