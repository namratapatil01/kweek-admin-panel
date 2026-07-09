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
        $attributes = $this->attributesToArray();
        if (empty($attributes['createdAt']) && !empty($attributes['created_at'])) {
            $attributes['createdAt'] = $attributes['created_at'];
        }
        if (empty($attributes['updatedAt']) && !empty($attributes['updated_at'])) {
            $attributes['updatedAt'] = $attributes['updated_at'];
        }

        $data = array_merge($attributes, $this->payload ?? []);
        unset($data['payload'], $data['created_at'], $data['updated_at']);

        return array_filter($data, static fn ($value) => $value !== null);
    }

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($value === null && is_array($this->payload) && isset($this->payload[$key])) {
            return $this->payload[$key];
        }

        return $value;
    }

    public function scopePublished($query)
    {
        return $query->where(function ($q) {
            $q->where('publish', true)
                ->orWhere('isActive', true)
                ->orWhere('isEnable', true)
                ->orWhere('isEnabled', true)
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.is_publish')) IN ('true', '1', 1)");
        });
    }
}
