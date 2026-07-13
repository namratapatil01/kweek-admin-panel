<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MapController extends Controller
{
    /**
     * Map current MySQL section IDs to legacy Firebase section IDs.
     */
    private const SECTION_LEGACY_ALIASES = [
        '3' => ['6285ddbfd9598'],      // Food
        '4' => ['631852d1bc978'],      // Taxi
        '6285ddbfd9598' => ['3'],
        '631852d1bc978' => ['4'],
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function multivendor()
    {
        return redirect()->route('map.food');
    }

    public function food(Request $request)
    {
        $sectionId = (string) ($request->cookie('section_id') ?: '3');

        return view('map.food', [
            'sectionName' => (string) (DB::table('sections')->where('id', $sectionId)->value('name') ?? 'Food'),
        ]);
    }

    public function parcel()
    {
        return redirect()->route('map.padala');
    }

    public function padala()
    {
        return view('map.padala', [
            'sectionName' => (string) (DB::table('sections')->where('id', '6')->value('name') ?? 'Padala'),
        ]);
    }

    public function rental()
    {
        return view('map.rental');
    }

    public function cab(Request $request)
    {
        $sectionId = (string) ($request->cookie('section_id') ?: '4');

        return match ($sectionId) {
            '8' => redirect()->route('map.toda'),
            default => redirect()->route('map.taxi'),
        };
    }

    public function taxi()
    {
        return view('map.taxi', [
            'sectionName' => (string) (DB::table('sections')->where('id', '4')->value('name') ?? 'Taxi'),
        ]);
    }

    public function toda()
    {
        return view('map.toda', [
            'sectionName' => (string) (DB::table('sections')->where('id', '8')->value('name') ?? 'TODA'),
        ]);
    }

    public function getMapSettings()
    {
        $raw = DB::table('settings')->where('id', 'DriverNearBy')->value('value');
        $data = $this->decodePayload($raw);

        return response()->json([
            'selectedMapType' => $data['selectedMapType'] ?? 'google',
        ]);
    }

    public function getDriverLocations(Request $request)
    {
        $ids = array_values(array_filter(array_map('trim', explode(',', (string) $request->input('ids', '')))));
        $serviceType = (string) $request->input('service_type', '');

        if (empty($ids)) {
            return response()->json(['drivers' => []]);
        }

        $query = DB::table('app_users')
            ->where('role', 'driver')
            ->whereIn('id', $ids);

        if ($serviceType !== '') {
            $query->where('serviceType', $serviceType);
        }

        $drivers = $query->get()
            ->map(fn ($driver) => $this->normalizeDriverForMap($driver))
            ->values();

        return response()->json(['drivers' => $drivers]);
    }

    public function getCabData(Request $request)
    {
        return $this->buildCabMapData((string) $request->input('section_id', '4'));
    }

    public function getTaxiData()
    {
        return $this->buildCabMapData('4');
    }

    public function getTodaData()
    {
        return $this->buildCabMapData('8');
    }

    public function getPadalaData()
    {
        return $this->buildParcelMapData('6');
    }

    protected function buildParcelMapData(string $sectionId)
    {
        $driversQuery = DB::table('app_users')
            ->where('role', 'driver')
            ->where('serviceType', 'parcel_delivery');

        $drivers = collect(
            $this->applySectionFilter($driversQuery, $sectionId, true)
                ->get()
                ->map(fn ($driver) => $this->normalizeDriverForMap($driver))
                ->all()
        );

        $ordersQuery = DB::table('parcel_orders')
            ->whereIn('status', ['In Transit', 'in_transit', 'Order Shipped'])
            ->orderByDesc('createdAt')
            ->limit(200);

        $orders = $this->applySectionFilter($ordersQuery, $sectionId)
            ->get()
            ->map(function ($order) {
                $payload = $this->decodePayload($order->payload ?? null);
                $driver = $this->extractRidePerson($order->driver ?? null, $payload['driver'] ?? null);
                $author = $this->extractRidePerson($order->author ?? null, $payload['author'] ?? null);

                return [
                    'id' => $order->id,
                    'status' => $order->status,
                    'flag' => 'in_transit',
                    'driver' => $driver,
                    'author' => $author,
                    'sender' => $payload['sender'] ?? null,
                    'receiver' => $payload['receiver'] ?? null,
                ];
            })
            ->values();

        $drivers = $this->mergeTransitDrivers($drivers, $orders, 'driver')->values();

        return response()->json([
            'drivers' => $drivers,
            'orders' => $orders,
            'section' => $this->getSectionMeta($sectionId),
        ]);
    }

    protected function buildCabMapData(string $sectionId)
    {
        $driversQuery = DB::table('app_users')
            ->where('role', 'driver')
            ->where('serviceType', 'cab-service');

        $drivers = collect(
            $this->applySectionFilter($driversQuery, $sectionId)
                ->get()
                ->map(fn ($driver) => $this->normalizeDriverForMap($driver))
                ->all()
        );

        $ridesQuery = DB::table('rides')
            ->whereIn('status', ['In Transit', 'in_transit'])
            ->orderByDesc('createdAt')
            ->limit(200);

        $rides = $this->applySectionFilter($ridesQuery, $sectionId)
            ->get()
            ->map(function ($ride) {
                $payload = $this->decodePayload($ride->payload ?? null);
                $driver = $this->extractRidePerson($ride->driver ?? null, $payload['driver'] ?? null);
                $author = $this->extractRidePerson($ride->author ?? null, $payload['author'] ?? null);

                return [
                    'id' => $ride->id,
                    'status' => $ride->status,
                    'flag' => 'in_transit',
                    'driver' => $driver,
                    'author' => $author,
                ];
            })
            ->values();

        $drivers = $this->mergeTransitDrivers($drivers, $rides, 'driver')->values();

        return response()->json([
            'drivers' => $drivers,
            'rides' => $rides,
            'section' => $this->getSectionMeta($sectionId),
        ]);
    }

    public function getMultivendorData(Request $request)
    {
        return $this->buildFoodMapData((string) $request->input('section_id', '3'));
    }

    public function getFoodData(Request $request)
    {
        return $this->buildFoodMapData((string) $request->input('section_id', '3'));
    }

    protected function buildFoodMapData(string $sectionId)
    {
        $driversQuery = DB::table('app_users')
            ->where('role', 'driver')
            ->where('serviceType', 'delivery-service');

        $drivers = collect(
            $this->applySectionFilter($driversQuery, $sectionId, true)
                ->get()
                ->map(fn ($driver) => $this->normalizeDriverForMap($driver))
                ->all()
        );

        $orders = DB::table('vendor_orders')
            ->whereIn('status', ['In Transit', 'in_transit', 'Order Shipped'])
            ->orderByDesc('createdAt')
            ->limit(200)
            ->get()
            ->map(function ($order) {
                $payload = $this->decodePayload($order->payload ?? null);
                $driver = $this->extractRidePerson($order->driver ?? null, $payload['driver'] ?? null);
                $author = $this->extractRidePerson($order->author ?? null, $payload['author'] ?? null);

                return [
                    'id' => $order->id,
                    'status' => $order->status,
                    'flag' => 'in_transit',
                    'driver' => $driver,
                    'author' => $author,
                    'vendor' => $payload['vendor'] ?? null,
                    'address' => $payload['address'] ?? (is_string($order->address ?? null) ? json_decode($order->address, true) : ($order->address ?? null)),
                ];
            })
            ->values();

        $drivers = $this->mergeTransitDrivers($drivers, $orders, 'driver')->values();

        return response()->json([
            'drivers' => $drivers,
            'orders' => $orders,
            'section' => $this->getSectionMeta($sectionId),
        ]);
    }

    protected function getSectionMeta(?string $sectionId): ?array
    {
        if ($sectionId === null || $sectionId === '') {
            return null;
        }

        $section = DB::table('sections')->where('id', $sectionId)->first(['id', 'name']);
        if (!$section) {
            return null;
        }

        return [
            'id' => $section->id,
            'name' => $section->name,
        ];
    }

    protected function extractRidePerson(mixed $columnValue, mixed $payloadValue): ?array
    {
        if (is_string($columnValue) && $columnValue !== '') {
            $decoded = json_decode($columnValue, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        if (is_array($columnValue)) {
            return $columnValue;
        }

        if (is_array($payloadValue)) {
            return $payloadValue;
        }

        return null;
    }

    protected function mergeTransitDrivers(Collection $drivers, Collection $transitItems, string $driverKey): Collection
    {
        $existingIds = $drivers->pluck('id')->filter()->all();

        foreach ($transitItems as $item) {
            $itemDriver = is_array($item) ? ($item[$driverKey] ?? null) : null;
            if (!is_array($itemDriver) || empty($itemDriver['id'])) {
                continue;
            }

            $driverId = (string) $itemDriver['id'];
            if (in_array($driverId, $existingIds, true)) {
                continue;
            }

            $dbDriver = DB::table('app_users')->where('id', $driverId)->first();
            if ($dbDriver) {
                $drivers->push($this->normalizeDriverForMap($dbDriver));
            } else {
                $drivers->push($this->normalizeDriverFromArray($itemDriver));
            }

            $existingIds[] = $driverId;
        }

        return $drivers;
    }

    protected function normalizeDriverFromArray(array $driver): array
    {
        $location = $driver['location'] ?? [];
        $lat = $driver['latitude']
            ?? ($location['latitude'] ?? null)
            ?? ($driver['coordinates']['latitude'] ?? null);
        $lng = $driver['longitude']
            ?? ($location['longitude'] ?? null)
            ?? ($driver['coordinates']['longitude'] ?? null);

        return [
            'id' => $driver['id'] ?? '',
            'firstName' => $driver['firstName'] ?? '',
            'lastName' => $driver['lastName'] ?? '',
            'phoneNumber' => $driver['phoneNumber'] ?? '',
            'carNumber' => $driver['carNumber'] ?? '',
            'carName' => $driver['carName'] ?? '',
            'serviceType' => $driver['serviceType'] ?? '',
            'sectionId' => $driver['sectionId'] ?? null,
            'isActive' => (bool) ($driver['isActive'] ?? false),
            'active' => (bool) ($driver['active'] ?? false),
            'latitude' => $lat !== null ? (float) $lat : null,
            'longitude' => $lng !== null ? (float) $lng : null,
            'location' => [
                'latitude' => $lat !== null ? (float) $lat : null,
                'longitude' => $lng !== null ? (float) $lng : null,
            ],
            'flag' => 'available',
        ];
    }

    protected function normalizeDriverForMap(object $driver): array
    {
        $payload = $this->decodePayload($driver->payload ?? null);

        $lat = $driver->latitude
            ?? ($payload['latitude'] ?? null)
            ?? ($payload['location']['latitude'] ?? null)
            ?? ($payload['coordinates']['latitude'] ?? null)
            ?? ($payload['g']['geopoint']['latitude'] ?? null);

        $lng = $driver->longitude
            ?? ($payload['longitude'] ?? null)
            ?? ($payload['location']['longitude'] ?? null)
            ?? ($payload['coordinates']['longitude'] ?? null)
            ?? ($payload['g']['geopoint']['longitude'] ?? null);

        return [
            'id' => $driver->id,
            'firstName' => $driver->firstName ?? ($payload['firstName'] ?? ''),
            'lastName' => $driver->lastName ?? ($payload['lastName'] ?? ''),
            'phoneNumber' => $driver->phoneNumber ?? ($payload['phoneNumber'] ?? ''),
            'carNumber' => $driver->carNumber ?? ($payload['carNumber'] ?? ''),
            'carName' => $driver->carName ?? ($payload['carName'] ?? ''),
            'serviceType' => $driver->serviceType ?? ($payload['serviceType'] ?? ''),
            'sectionId' => $driver->sectionId ?? ($driver->section_id ?? ($payload['sectionId'] ?? null)),
            'isActive' => (bool) ($driver->isActive ?? $payload['isActive'] ?? false),
            'active' => (bool) ($driver->active ?? $payload['active'] ?? false),
            'latitude' => $lat !== null ? (float) $lat : null,
            'longitude' => $lng !== null ? (float) $lng : null,
            'location' => [
                'latitude' => $lat !== null ? (float) $lat : null,
                'longitude' => $lng !== null ? (float) $lng : null,
            ],
            'flag' => 'available',
        ];
    }

    protected function hasValidCoordinates(array $driver): bool
    {
        $lat = $driver['location']['latitude'] ?? null;
        $lng = $driver['location']['longitude'] ?? null;

        if ($lat === null || $lng === null) {
            return false;
        }

        $lat = (float) $lat;
        $lng = (float) $lng;

        if (abs($lat) < 0.1 && abs($lng) < 0.1) {
            return false;
        }

        if (abs($lat - 0.01) < 0.001 && abs($lng - 0.01) < 0.001) {
            return false;
        }

        if ($lat < -60 || $lat > 80 || $lng < -180 || $lng > 180) {
            return false;
        }

        return true;
    }

    protected function sectionIdsForFilter(?string $sectionId): array
    {
        if ($sectionId === null || $sectionId === '') {
            return [];
        }

        $sectionId = (string) $sectionId;
        $ids = [$sectionId];

        if (isset(self::SECTION_LEGACY_ALIASES[$sectionId])) {
            $ids = array_merge($ids, self::SECTION_LEGACY_ALIASES[$sectionId]);
        }

        return array_values(array_unique($ids));
    }

    protected function applySectionFilter($query, ?string $sectionId, bool $includeUnassigned = false)
    {
        $ids = $this->sectionIdsForFilter($sectionId);
        if (empty($ids)) {
            return $query;
        }

        return $query->where(function ($q) use ($ids, $includeUnassigned) {
            foreach ($ids as $id) {
                $q->orWhere('sectionId', $id)
                    ->orWhere('section_id', $id)
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.sectionId')) = ?", [$id]);
            }

            if ($includeUnassigned) {
                $q->orWhereNull('sectionId')
                    ->orWhere('sectionId', '')
                    ->orWhereNull('section_id')
                    ->orWhere('section_id', '');
            }
        });
    }

    protected function decodePayload(mixed $payload): array
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (is_string($payload) && $payload !== '') {
            $decoded = json_decode($payload, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
