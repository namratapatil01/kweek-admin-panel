<?php

namespace App\Support;

class PayloadMapper
{
    /**
     * Split incoming document fields into table columns and overflow payload.
     *
     * @param  array<int, string>  $fillableColumns
     * @param  array<int, string>  $jsonColumns
     * @return array{attributes: array<string, mixed>, overflow: array<string, mixed>}
     */
    public static function map(array $data, array $fillableColumns, array $jsonColumns = ['payload']): array
    {
        $attributes = [];
        $overflow = [];

        foreach ($data as $key => $value) {
            if ($value === '') {
                $value = null;
            }

            if (in_array($key, $fillableColumns, true)) {
                if (is_array($value) && ! in_array($key, $jsonColumns, true)) {
                    $overflow[$key] = $value;
                    continue;
                }

                $attributes[$key] = $value;
            } elseif (in_array($key, $jsonColumns, true)) {
                $attributes[$key] = is_array($value) ? $value : json_decode((string) $value, true);
            } else {
                $overflow[$key] = $value;
            }
        }

        return compact('attributes', 'overflow');
    }

    public static function parseTimestamp(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            if (($value['__datatype__'] ?? null) === 'timestamp') {
                return date('Y-m-d H:i:s', (int) ($value['value']['_seconds'] ?? time()));
            }
            if (isset($value['_seconds'])) {
                return date('Y-m-d H:i:s', (int) $value['_seconds']);
            }
        }

        if (is_numeric($value)) {
            return date('Y-m-d H:i:s', (int) $value);
        }

        if (is_string($value)) {
            $timestamp = strtotime($value);

            return $timestamp !== false ? date('Y-m-d H:i:s', $timestamp) : null;
        }

        return null;
    }
}
