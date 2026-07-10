<?php

namespace App\Services\Driver;

use App\Models\AppUser;
use App\Models\ParcelOrder;
use App\Models\RentalOrder;
use App\Models\Ride;
use App\Models\VendorOrder;
use Illuminate\Database\Eloquent\Model;

class DriverTrackingService
{
    protected array $models = [
        'vendor' => VendorOrder::class,
        'ride' => Ride::class,
        'parcel' => ParcelOrder::class,
        'rental' => RentalOrder::class,
    ];

    public function trackOrder(AppUser $driver, string $type, string $orderId): ?array
    {
        $model = $this->models[$type] ?? null;

        if (! $model) {
            return null;
        }

        $order = $model::query()->where('id', $orderId)->first();

        if (! $order) {
            return null;
        }

        $orderData = $order->toDocumentArray();
        $driverId = $orderData['driverId'] ?? $orderData['driverID'] ?? $driver->id;

        $driverUser = AppUser::query()->find($driverId);
        $driverDoc = $driverUser?->toDocumentArray();

        return [
            'order' => $orderData,
            'driver' => $driverDoc,
            'driverLocation' => $driverDoc ? [
                'latitude' => $driverDoc['latitude'] ?? null,
                'longitude' => $driverDoc['longitude'] ?? null,
                'rotation' => $driverDoc['rotation'] ?? data_get($driverDoc, 'payload.rotation'),
            ] : null,
        ];
    }
}
