<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function multivendor()
    {
        return view('map.multivendor');
    }

    public function parcel()
    {
        return view('map.parcel');
    }

    public function rental()
    {
        return view('map.rental');
    }

    public function cab()
    {
        return view('map.cab');
    }

    public function getCabData(Request $request)
    {
        $sectionId = $request->input('section_id');

        $drivers = DB::table('app_users')
            ->where('role', 'driver')
            ->when($sectionId, fn ($q) => $q->where('sectionId', $sectionId))
            ->get()
            ->map(fn ($driver) => $this->normalizeDriverForMap($driver))
            ->filter(fn ($driver) => $this->hasValidCoordinates($driver))
            ->values();

        $rides = DB::table('rides')
            ->when($sectionId, fn ($q) => $q->where('sectionId', $sectionId))
            ->whereIn('status', ['In Transit', 'in_transit'])
            ->get();

        return response()->json([
            'drivers' => $drivers,
            'rides' => $rides,
        ]);
    }

    public function getMultivendorData(Request $request)
    {
        $drivers = DB::table('app_users')
            ->where('role', 'driver')
            ->where('serviceType', 'delivery-service')
            ->get()
            ->map(fn ($driver) => $this->normalizeDriverForMap($driver))
            ->filter(fn ($driver) => $this->hasValidCoordinates($driver))
            ->values();

        $orders = DB::table('vendor_orders')
            ->whereIn('status', ['In Transit', 'in_transit', 'Order Shipped'])
            ->orderByDesc('createdAt')
            ->limit(200)
            ->get()
            ->map(function ($order) {
                $payload = $this->decodePayload($order->payload ?? null);
                $driver = $payload['driver'] ?? null;
                if (is_string($order->driver ?? null)) {
                    $decodedDriver = json_decode($order->driver, true);
                    if (is_array($decodedDriver)) {
                        $driver = $decodedDriver;
                    }
                } elseif (is_array($order->driver ?? null)) {
                    $driver = $order->driver;
                }

                $author = $payload['author'] ?? null;
                if (is_string($order->author ?? null)) {
                    $decodedAuthor = json_decode($order->author, true);
                    if (is_array($decodedAuthor)) {
                        $author = $decodedAuthor;
                    }
                } elseif (is_array($order->author ?? null)) {
                    $author = $order->author;
                }

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

        return response()->json([
            'drivers' => $drivers,
            'orders' => $orders,
        ]);
    }

    protected function normalizeDriverForMap(object $driver): array
    {
        $payload = $this->decodePayload($driver->payload ?? null);

        $lat = $driver->latitude
            ?? ($payload['latitude'] ?? null)
            ?? ($payload['location']['latitude'] ?? null)
            ?? ($payload['coordinates']['latitude'] ?? null);

        $lng = $driver->longitude
            ?? ($payload['longitude'] ?? null)
            ?? ($payload['location']['longitude'] ?? null)
            ?? ($payload['coordinates']['longitude'] ?? null);

        return [
            'id' => $driver->id,
            'firstName' => $driver->firstName ?? ($payload['firstName'] ?? ''),
            'lastName' => $driver->lastName ?? ($payload['lastName'] ?? ''),
            'phoneNumber' => $driver->phoneNumber ?? ($payload['phoneNumber'] ?? ''),
            'serviceType' => $driver->serviceType ?? ($payload['serviceType'] ?? ''),
            'sectionId' => $driver->sectionId ?? ($payload['sectionId'] ?? null),
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

        // Ignore placeholder / empty coordinates
        if (abs($lat) < 0.1 && abs($lng) < 0.1) {
            return false;
        }

        return true;
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