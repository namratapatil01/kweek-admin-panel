<?php

namespace App\Services\Driver;

use App\Models\AppUser;
use App\Models\ParcelOrder;
use App\Models\RentalOrder;
use App\Models\Ride;
use App\Models\Wallet;
use App\Services\Storage\FileStorageService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DriverOwnerService
{
    public function __construct(
        protected DriverAuthService $authService,
        protected FileStorageService $storageService
    ) {
    }

    public function assertOwner(AppUser $driver): void
    {
        if (! $driver->isOwner) {
            throw ValidationException::withMessages([
                'isOwner' => ['Only fleet owners can access this resource.'],
            ]);
        }
    }

    public function listDrivers(AppUser $owner, int $perPage = 20): LengthAwarePaginator
    {
        $this->assertOwner($owner);

        return AppUser::query()
            ->where('role', 'driver')
            ->where('ownerId', $owner->id)
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function showDriver(AppUser $owner, string $driverId): ?array
    {
        $this->assertOwner($owner);

        $driver = AppUser::query()
            ->where('id', $driverId)
            ->where('ownerId', $owner->id)
            ->where('role', 'driver')
            ->first();

        return $driver?->toDocumentArray();
    }

    public function createDriver(AppUser $owner, array $data): array
    {
        $this->assertOwner($owner);

        $password = $data['password'] ?? Str::random(12);

        $result = $this->authService->register(array_merge($data, [
            'isOwner' => false,
            'ownerId' => $owner->id,
            'serviceType' => $data['serviceType'] ?? $owner->serviceType,
            'sectionId' => $data['sectionId'] ?? $owner->sectionId,
            'section_id' => $data['section_id'] ?? $owner->section_id,
            'zoneId' => $data['zoneId'] ?? $owner->zoneId,
            'password' => $password,
            'password_confirmation' => $password,
        ]));

        return [
            'driver' => $result['user']->toDocumentArray(),
            'token' => $result['token'],
            'pending_approval' => $result['pending_approval'] ?? false,
        ];
    }

    public function updateDriver(AppUser $owner, string $driverId, array $data): AppUser
    {
        $this->assertOwner($owner);

        $driver = AppUser::query()
            ->where('id', $driverId)
            ->where('ownerId', $owner->id)
            ->where('role', 'driver')
            ->first();

        if (! $driver) {
            throw ValidationException::withMessages(['id' => ['Fleet driver not found.']]);
        }

        $allowed = [
            'firstName', 'lastName', 'phoneNumber', 'countryCode',
            'active', 'carName', 'carNumber', 'carMakes', 'vehicleType',
            'vehicleId', 'rideType', 'zoneId',
        ];
        $update = array_intersect_key($data, array_flip($allowed));

        if (isset($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        if ($update !== []) {
            $driver->update($update);
        }

        return $driver->fresh();
    }

    public function uploadDriverImage(AppUser $owner, string $driverId, $file): AppUser
    {
        $this->assertOwner($owner);

        $driver = AppUser::query()
            ->where('id', $driverId)
            ->where('ownerId', $owner->id)
            ->where('role', 'driver')
            ->first();

        if (! $driver) {
            throw ValidationException::withMessages(['id' => ['Fleet driver not found.']]);
        }

        $result = $this->storageService->upload($file, 'profileImage/' . $driver->id, 'public');
        $driver->update(['profilePictureURL' => url($result['url'])]);

        return $driver->fresh();
    }

    public function deleteDriver(AppUser $owner, string $driverId): void
    {
        $this->assertOwner($owner);

        $driver = AppUser::query()
            ->where('id', $driverId)
            ->where('ownerId', $owner->id)
            ->where('role', 'driver')
            ->first();

        if (! $driver) {
            throw ValidationException::withMessages(['id' => ['Fleet driver not found.']]);
        }

        $driver->tokens()->delete();
        $driver->update([
            'active' => false,
            'isActive' => false,
            'fcmToken' => null,
            'email' => 'deleted_' . $driver->id . '_' . ($driver->email ?? ''),
        ]);
        $driver->mergePayload(['deleted_at' => now()->toIso8601String()]);
        $driver->save();
    }

    public function driverLocations(AppUser $owner): array
    {
        $this->assertOwner($owner);

        return AppUser::query()
            ->where('role', 'driver')
            ->where('ownerId', $owner->id)
            ->where('isOwner', false)
            ->get()
            ->map(function (AppUser $driver) {
                $doc = $driver->toDocumentArray();

                return [
                    'id' => $driver->id,
                    'firstName' => $doc['firstName'] ?? null,
                    'lastName' => $doc['lastName'] ?? null,
                    'latitude' => $doc['latitude'] ?? null,
                    'longitude' => $doc['longitude'] ?? null,
                    'rotation' => $doc['rotation'] ?? data_get($doc, 'payload.rotation'),
                    'isActive' => (bool) $driver->isActive,
                    'lastOnlineTimestamp' => $doc['lastOnlineTimestamp'] ?? null,
                    'carNumber' => $doc['carNumber'] ?? null,
                ];
            })
            ->values()
            ->all();
    }

    public function dashboard(AppUser $owner): array
    {
        $this->assertOwner($owner);

        $drivers = AppUser::query()
            ->where('role', 'driver')
            ->where('ownerId', $owner->id)
            ->where('isOwner', false)
            ->get();

        $driverIds = $drivers->pluck('id')->all();
        $completedStatus = ['Order Completed', 'Completed', 'completed'];

        $rideCount = Ride::query()->whereIn('driverId', $driverIds)->whereIn('status', $completedStatus)->count();
        $parcelCount = ParcelOrder::query()->whereIn('driverId', $driverIds)->whereIn('status', $completedStatus)->count();
        $rentalCount = RentalOrder::query()->whereIn('driverId', $driverIds)->whereIn('status', $completedStatus)->count();

        $earnings = Wallet::query()
            ->whereIn('user_id', array_merge([$owner->id], $driverIds))
            ->where('isTopUp', true)
            ->sum('amount');

        return [
            'owner' => $owner->toDocumentArray(),
            'drivers_count' => $drivers->count(),
            'active_drivers' => $drivers->where('isActive', true)->count(),
            'completed_orders' => [
                'rides' => $rideCount,
                'parcels' => $parcelCount,
                'rentals' => $rentalCount,
                'total' => $rideCount + $parcelCount + $rentalCount,
            ],
            'total_earnings' => (float) $earnings,
            'wallet_amount' => (float) ($owner->wallet_amount ?? 0),
        ];
    }
}
