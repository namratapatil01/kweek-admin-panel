<?php

namespace App\Services\Provider;

use App\Models\AppUser;
use App\Services\Storage\FileStorageService;

class ProviderProfileService
{
    public function __construct(protected FileStorageService $storageService)
    {
    }

    public function update(AppUser $user, array $data): AppUser
    {
        $allowed = [
            'firstName', 'lastName', 'phoneNumber', 'countryCode',
            'sectionId', 'section_id', 'latitude', 'longitude', 'fcmToken',
            'userBankDetails',
        ];

        $update = array_intersect_key($data, array_flip($allowed));

        if (isset($update['sectionId']) && ! isset($update['section_id'])) {
            $update['section_id'] = $update['sectionId'];
        }

        if (isset($update['section_id']) && ! isset($update['sectionId'])) {
            $update['sectionId'] = $update['section_id'];
        }

        $payloadExtras = array_intersect_key($data, array_flip(['street', 'area']));

        if ($update !== []) {
            $user->update($update);
        }

        if ($payloadExtras !== []) {
            $user->mergePayload($payloadExtras);
            $user->save();
        }

        return $user->fresh();
    }

    public function uploadProfileImage(AppUser $user, $file): AppUser
    {
        $result = $this->storageService->upload(
            $file,
            'profileImage/' . $user->id,
            'public'
        );

        $url = url($result['url']);
        $user->update(['profilePictureURL' => $url]);

        return $user->fresh();
    }

    public function updateBankDetails(AppUser $user, array $bankDetails): AppUser
    {
        $user->update(['userBankDetails' => $bankDetails]);

        return $user->fresh();
    }
}
