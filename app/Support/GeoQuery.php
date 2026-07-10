<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GeoQuery
{
    public static function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    public static function extractLatLng(array $doc): ?array
    {
        $paths = [
            ['sourcePoint', 'geopoint', 'latitude'],
            ['sourcePoint', 'geopoint', '_latitude'],
            ['sourcePoint', 'latitude'],
            ['sourceLocation', 'latitude'],
            ['senderLatLong', 'latitude'],
            ['address', 'latitude'],
            ['payload', 'sourcePoint', 'geopoint', 'latitude'],
        ];

        foreach ($paths as $latPath) {
            $lngPath = array_merge(array_slice($latPath, 0, -1), ['longitude']);
            if (count($latPath) === 1) {
                continue;
            }

            $lat = data_get($doc, implode('.', $latPath));
            $lng = data_get($doc, implode('.', $lngPath));

            if ($lat !== null && $lng !== null) {
                return [(float) $lat, (float) $lng];
            }
        }

        return null;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @return Collection<int, array<string, mixed>>
     */
    public static function filterByRadius(Collection $items, float $lat, float $lng, float $radiusKm): Collection
    {
        return $items->filter(function (array $item) use ($lat, $lng, $radiusKm) {
            $coords = self::extractLatLng($item);

            if (! $coords) {
                return false;
            }

            return self::haversineKm($lat, $lng, $coords[0], $coords[1]) <= $radiusKm;
        })->values();
    }

    public static function rejectByDriver(Builder $query, string $driverId): Builder
    {
        return $query->where(function ($q) use ($driverId) {
            $q->whereNull('payload->rejectedByDrivers')
                ->orWhereJsonDoesntContain('payload->rejectedByDrivers', $driverId);
        });
    }
}
