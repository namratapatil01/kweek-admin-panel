<?php

namespace App\Services\Driver;

use App\Models\AppUser;
use App\Services\Storage\FileStorageService;
use Illuminate\Validation\ValidationException;

class DriverProfileService
{
    public function __construct(protected FileStorageService $storageService)
    {
    }

    public function update(AppUser $driver, array $data): AppUser
    {
        $allowed = [
            'firstName', 'lastName', 'phoneNumber', 'countryCode',
            'sectionId', 'section_id', 'zoneId', 'latitude', 'longitude',
            'fcmToken', 'userBankDetails', 'carName', 'carNumber', 'carPictureURL',
            'carMakes', 'vehicleType', 'vehicleId', 'rideType', 'vendorID',
        ];

        $update = array_intersect_key($data, array_flip($allowed));

        if (isset($update['sectionId']) && ! isset($update['section_id'])) {
            $update['section_id'] = $update['sectionId'];
        }
        if (isset($update['section_id']) && ! isset($update['sectionId'])) {
            $update['sectionId'] = $update['section_id'];
        }

        $payloadExtras = array_intersect_key($data, array_flip(['rotation', 'orderRequestData', 'inProgressOrderID', 'orderCabRequestData']));

        if ($update !== []) {
            $driver->update($update);
        }
        if ($payloadExtras !== []) {
            $driver->mergePayload($payloadExtras);
            $driver->save();
        }

        return $driver->fresh();
    }

    public function setOnline(AppUser $driver, bool $online): AppUser
    {
        $driver->update(['isActive' => $online]);
        if (! $online) {
            $driver->mergePayload(['lastOfflineAt' => now()->toIso8601String()]);
            $driver->save();
        }

        return $driver->fresh();
    }

    public function updateLocation(AppUser $driver, array $data): AppUser
    {
        if (! $driver->isActive) {
            throw ValidationException::withMessages([
                'isActive' => ['Driver must be online to update location.'],
            ]);
        }

        $driver->update([
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'lastOnlineTimestamp' => now(),
        ]);

        if (isset($data['rotation'])) {
            $driver->mergePayload(['rotation' => $data['rotation']]);
            $driver->save();
        }

        return $driver->fresh();
    }

    public function uploadProfileImage(AppUser $driver, $file): AppUser
    {
        $result = $this->storageService->upload($file, 'profileImage/' . $driver->id, 'public');
        $driver->update(['profilePictureURL' => url($result['url'])]);

        return $driver->fresh();
    }

    public function updateBankDetails(AppUser $driver, array $bankDetails): AppUser
    {
        $driver->update(['userBankDetails' => $bankDetails]);

        return $driver->fresh();
    }
}
