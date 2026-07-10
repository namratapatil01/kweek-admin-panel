<?php

namespace App\Services\Vendor;

use App\Models\AppUser;
use App\Models\Vendor;
use App\Services\Storage\FileStorageService;
use App\Support\CatalogEntityWriter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VendorProfileService
{
    public function __construct(protected FileStorageService $storageService)
    {
    }

    public function getStore(AppUser $user): ?Vendor
    {
        if (! $user->vendorID) {
            return null;
        }

        return Vendor::query()->find($user->vendorID);
    }

    public function requireStore(AppUser $user): Vendor
    {
        $store = $this->getStore($user);
        if (! $store) {
            throw ValidationException::withMessages(['vendorID' => ['Store not set up yet.']]);
        }

        return $store;
    }

    public function updateUser(AppUser $user, array $data): AppUser
    {
        $allowed = [
            'firstName', 'lastName', 'phoneNumber', 'countryCode',
            'sectionId', 'section_id', 'zoneId', 'latitude', 'longitude', 'fcmToken',
            'userBankDetails',
        ];

        $update = array_intersect_key($data, array_flip($allowed));
        if (isset($update['sectionId']) && ! isset($update['section_id'])) {
            $update['section_id'] = $update['sectionId'];
        }

        if ($update !== []) {
            $user->update($update);
        }

        return $user->fresh();
    }

    public function uploadUserImage(AppUser $user, $file): AppUser
    {
        $result = $this->storageService->upload($file, 'profileImage/' . $user->id, 'public');
        $user->update(['profilePictureURL' => url($result['url'])]);

        return $user->fresh();
    }

    public function updateBankDetails(AppUser $user, array $bankDetails): AppUser
    {
        $user->update(['userBankDetails' => $bankDetails]);

        return $user->fresh();
    }

    public function createStore(AppUser $user, array $data): Vendor
    {
        if ($user->vendorID && Vendor::query()->find($user->vendorID)) {
            throw ValidationException::withMessages(['vendorID' => ['Store already exists for this account.']]);
        }

        $vendorId = $data['id'] ?? (string) Str::uuid();
        $payload = array_merge($data, [
            'author' => $user->id,
            'authorName' => trim(($user->firstName ?? '') . ' ' . ($user->lastName ?? '')),
        ]);

        $store = CatalogEntityWriter::write(new Vendor(), array_merge($data, [
            'id' => $vendorId,
            'section_id' => $data['section_id'] ?? $data['sectionId'] ?? $user->section_id ?? $user->sectionId,
            'sectionId' => $data['sectionId'] ?? $data['section_id'] ?? $user->sectionId ?? $user->section_id,
            'fcmToken' => $user->fcmToken,
            'createdAt' => now(),
            'walletAmount' => 0,
            'reviewsCount' => 0,
            'reviewsSum' => 0,
            'reststatus' => $data['reststatus'] ?? true,
        ]));

        $store->mergePayload($payload);
        $store->save();

        $user->update([
            'vendorID' => $vendorId,
            'sectionId' => $store->section_id ?? $store->sectionId ?? $user->sectionId,
            'section_id' => $store->section_id ?? $store->sectionId ?? $user->section_id,
            'zoneId' => $data['zoneId'] ?? $user->zoneId,
        ]);

        return $store->fresh();
    }

    public function updateStore(AppUser $user, array $data): Vendor
    {
        $store = $this->requireStore($user);
        unset($data['id'], $data['author']);

        $store = CatalogEntityWriter::write(new Vendor(), $data, $store);

        return $store->fresh();
    }

    public function uploadStoreImage(AppUser $user, $file, string $type = 'photo'): Vendor
    {
        $store = $this->requireStore($user);
        $result = $this->storageService->upload($file, 'profileImage/' . $user->id, 'public');
        $url = url($result['url']);

        if ($type === 'cover' || $type === 'photos') {
            $photos = $store->photos ?? [];
            if (! is_array($photos)) {
                $photos = [];
            }
            $photos[] = $url;
            $store->update(['photos' => $photos]);
        } else {
            $store->update(['photo' => $url]);
        }

        return $store->fresh();
    }
}
