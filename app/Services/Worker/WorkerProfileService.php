<?php

namespace App\Services\Worker;

use App\Models\AppUser;
use App\Models\ProviderWorker;
use App\Services\Storage\FileStorageService;
use App\Support\CatalogEntityWriter;
use Illuminate\Validation\ValidationException;

class WorkerProfileService
{
    public function __construct(
        protected WorkerAuthService $authService,
        protected FileStorageService $storageService
    ) {
    }

    public function getWorkerOrFail(AppUser $user): ProviderWorker
    {
        $worker = $this->authService->resolveWorker($user);

        if (! $worker) {
            throw ValidationException::withMessages([
                'worker' => ['Worker profile not found.'],
            ]);
        }

        return $worker;
    }

    public function update(AppUser $user, array $data): ProviderWorker
    {
        $worker = $this->getWorkerOrFail($user);

        $allowed = [
            'firstName', 'lastName', 'phoneNumber', 'address',
            'latitude', 'longitude', 'fcmToken', 'online', 'profilePictureURL',
        ];

        $update = array_intersect_key($data, array_flip($allowed));

        if ($update !== []) {
            $worker = CatalogEntityWriter::write(new ProviderWorker(), $update, $worker);
        }

        $this->authService->syncAppUser($worker, null, $data['fcmToken'] ?? null);

        return $worker->fresh();
    }

    public function setOnline(AppUser $user, bool $online): ProviderWorker
    {
        $worker = $this->getWorkerOrFail($user);
        $worker->mergePayload(['online' => $online]);
        $worker->save();

        $this->authService->syncAppUser($worker);

        return $worker->fresh();
    }

    public function uploadProfileImage(AppUser $user, $file): ProviderWorker
    {
        $worker = $this->getWorkerOrFail($user);

        $result = $this->storageService->upload(
            $file,
            'worker/profileImage/' . $worker->id,
            'public'
        );

        $url = url($result['url']);
        $worker->mergePayload(['profilePictureURL' => $url]);
        $worker->save();

        $this->authService->syncAppUser($worker);

        return $worker->fresh();
    }

    public function providerInfo(AppUser $user): ?array
    {
        $worker = $this->getWorkerOrFail($user);
        $providerId = $worker->toDocumentArray()['providerId'] ?? null;

        if (! $providerId) {
            return null;
        }

        $provider = AppUser::query()
            ->where('id', $providerId)
            ->where('role', 'provider')
            ->first();

        return $provider?->toDocumentArray();
    }
}
