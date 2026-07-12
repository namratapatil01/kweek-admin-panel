<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CatalogEntityWriter
{
    /**
     * Create or update a Kweek catalog/order model, mapping known columns and overflowing extras into payload.
     */
    public static function write(Model $prototype, array $data, ?Model $existing = null): Model
    {
        $table = $prototype->getTable();
        $fillable = array_diff(Schema::getColumnListing($table), ['created_at', 'updated_at']);
        $jsonColumns = array_values(array_filter($fillable, function ($column) use ($prototype) {
            $casts = $prototype->getCasts();

            return ($casts[$column] ?? null) === 'array' || $column === 'payload';
        }));

        if (! in_array('payload', $jsonColumns, true)) {
            $jsonColumns[] = 'payload';
        }

        $incomingPayload = [];
        if (isset($data['payload']) && is_array($data['payload'])) {
            $incomingPayload = $data['payload'];
            unset($data['payload']);
        }

        $mapped = PayloadMapper::map($data, $fillable, $jsonColumns);
        $attributes = $mapped['attributes'];

        $overflow = array_merge($incomingPayload, $mapped['overflow']);
        if ($overflow !== []) {
            $existingPayload = [];
            if ($existing && is_array($existing->payload ?? null)) {
                $existingPayload = $existing->payload;
            } elseif (is_array($attributes['payload'] ?? null)) {
                $existingPayload = $attributes['payload'];
            }
            $attributes['payload'] = array_merge($existingPayload, $overflow);
        }

        if (empty($attributes['id'])) {
            $attributes['id'] = $existing?->id ?? (string) Str::uuid();
        }

        if ($existing) {
            $existing->fill($attributes);
            $existing->save();

            return $existing->fresh();
        }

        return $prototype->newQuery()->create($attributes);
    }
}
