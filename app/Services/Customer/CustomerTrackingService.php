<?php

namespace App\Services\Customer;

use App\Models\AppUser;
use App\Models\BookedTable;
use App\Models\ParcelOrder;
use App\Models\ProviderOrder;
use App\Models\RentalOrder;
use App\Models\Ride;
use App\Models\VendorOrder;
use Illuminate\Database\Eloquent\Model;

class CustomerTrackingService
{
    protected array $orderModels = [
        'vendor' => VendorOrder::class,
        'parcel' => ParcelOrder::class,
        'rental' => RentalOrder::class,
        'ride' => Ride::class,
        'provider' => ProviderOrder::class,
        'dine-in' => BookedTable::class,
    ];

    public function trackOrder(string $customerId, string $type, string $orderId): ?array
    {
        $order = $this->findOrder($customerId, $type, $orderId);

        if (! $order) {
            return null;
        }

        $orderData = $order->toDocumentArray();
        $driverId = $orderData['driverId'] ?? $orderData['driverID'] ?? null;

        $driver = null;
        if ($driverId) {
            $driverUser = AppUser::query()->find($driverId);
            if ($driverUser) {
                $driver = $driverUser->toDocumentArray();
            }
        }

        return [
            'order' => $orderData,
            'driver' => $driver,
            'driverLocation' => $driver ? [
                'latitude' => $driver['latitude'] ?? null,
                'longitude' => $driver['longitude'] ?? null,
                'rotation' => $driver['rotation'] ?? data_get($driver, 'payload.rotation'),
            ] : null,
        ];
    }

    public function driverLocation(string $driverId): ?array
    {
        $driver = AppUser::query()->find($driverId);

        if (! $driver) {
            return null;
        }

        $doc = $driver->toDocumentArray();

        return [
            'id' => $driver->id,
            'latitude' => $doc['latitude'] ?? null,
            'longitude' => $doc['longitude'] ?? null,
            'rotation' => $doc['rotation'] ?? data_get($doc, 'payload.rotation'),
            'lastOnlineTimestamp' => $doc['lastOnlineTimestamp'] ?? null,
        ];
    }

    protected function findOrder(string $customerId, string $type, string $orderId): ?Model
    {
        $modelClass = $this->orderModels[$type] ?? null;

        if (! $modelClass) {
            return null;
        }

        return $modelClass::query()
            ->where('authorID', $customerId)
            ->find($orderId);
    }
}
