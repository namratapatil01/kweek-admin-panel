<?php

namespace App\Services\Vendor;

use App\Models\AppUser;
use App\Services\Driver\DriverAuthService;
use App\Services\Storage\FileStorageService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VendorDriverService
{
    public function __construct(
        protected DriverAuthService $driverAuthService,
        protected FileStorageService $storageService
    ) {
    }

    public function list(AppUser $vendor, int $perPage = 20): LengthAwarePaginator
    {
        $this->requireVendorId($vendor);

        return AppUser::query()
            ->where('role', 'driver')
            ->where('vendorID', $vendor->vendorID)
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function show(AppUser $vendor, string $driverId): ?array
    {
        $driver = $this->findDriver($vendor, $driverId);

        return $driver?->toDocumentArray();
    }

    public function create(AppUser $vendor, array $data): array
    {
        $this->requireVendorId($vendor);
        $password = $data['password'] ?? Str::random(12);

        $result = $this->driverAuthService->register(array_merge($data, [
            'vendorID' => $vendor->vendorID,
            'serviceType' => 'delivery-service',
            'sectionId' => $data['sectionId'] ?? $vendor->sectionId,
            'section_id' => $data['section_id'] ?? $vendor->section_id,
            'zoneId' => $data['zoneId'] ?? $vendor->zoneId,
            'password' => $password,
            'password_confirmation' => $password,
            'isOwner' => false,
        ]));

        return [
            'driver' => $result['user']->toDocumentArray(),
            'token' => $result['token'],
            'pending_approval' => $result['pending_approval'] ?? false,
        ];
    }

    public function update(AppUser $vendor, string $driverId, array $data): AppUser
    {
        $driver = $this->findDriver($vendor, $driverId);
        if (! $driver) {
            throw ValidationException::withMessages(['id' => ['Driver not found.']]);
        }

        $allowed = ['firstName', 'lastName', 'phoneNumber', 'active', 'carName', 'carNumber', 'vehicleType', 'vehicleId'];
        $update = array_intersect_key($data, array_flip($allowed));
        if (isset($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }
        if ($update !== []) {
            $driver->update($update);
        }

        return $driver->fresh();
    }

    public function uploadImage(AppUser $vendor, string $driverId, $file): AppUser
    {
        $driver = $this->findDriver($vendor, $driverId);
        if (! $driver) {
            throw ValidationException::withMessages(['id' => ['Driver not found.']]);
        }

        $result = $this->storageService->upload($file, 'profileImage/' . $driver->id, 'public');
        $driver->update(['profilePictureURL' => url($result['url'])]);

        return $driver->fresh();
    }

    protected function requireVendorId(AppUser $vendor): string
    {
        if (! $vendor->vendorID) {
            throw ValidationException::withMessages(['vendorID' => ['Store not set up yet.']]);
        }

        return $vendor->vendorID;
    }

    protected function findDriver(AppUser $vendor, string $driverId): ?AppUser
    {
        if (! $vendor->vendorID) {
            return null;
        }

        return AppUser::query()
            ->where('id', $driverId)
            ->where('role', 'driver')
            ->where('vendorID', $vendor->vendorID)
            ->first();
    }
}
