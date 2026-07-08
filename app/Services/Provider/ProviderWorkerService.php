<?php

namespace App\Services\Provider;

use App\Models\AppUser;
use App\Models\ProviderWorker;
use App\Support\CatalogEntityWriter;
use App\Services\Storage\FileStorageService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class ProviderWorkerService
{
    public function __construct(protected FileStorageService $storageService)
    {
    }

    public function list(string $providerId, ?bool $onlineOnly = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = ProviderWorker::query()->where('providerId', $providerId);

        if ($onlineOnly === true) {
            $query->where(function ($q) {
                $q->where('payload->online', true)->orWhere('isActive', true);
            });
        }

        return $query->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function show(string $providerId, string $id): ?array
    {
        $worker = ProviderWorker::query()
            ->where('id', $id)
            ->where('providerId', $providerId)
            ->first();

        return $worker?->toDocumentArray();
    }

    public function create(AppUser $provider, array $data): array
    {
        $data['providerId'] = $provider->id;
        $data['createdAt'] = $data['createdAt'] ?? now();
        $data['active'] = $data['active'] ?? true;
        $data['online'] = $data['online'] ?? false;
        $data['reviewsCount'] = $data['reviewsCount'] ?? 0;
        $data['reviewsSum'] = $data['reviewsSum'] ?? 0;

        if (! empty($data['password'])) {
            $data['password_hash'] = Hash::make($data['password']);
            unset($data['password']);
        }

        $worker = CatalogEntityWriter::write(new ProviderWorker(), $data);

        return $worker->toDocumentArray();
    }

    public function update(string $providerId, string $id, array $data): ?array
    {
        $worker = ProviderWorker::query()
            ->where('id', $id)
            ->where('providerId', $providerId)
            ->first();

        if (! $worker) {
            return null;
        }

        unset($data['providerId'], $data['id']);

        if (! empty($data['password'])) {
            $data['password_hash'] = Hash::make($data['password']);
            unset($data['password']);
        }

        $worker = CatalogEntityWriter::write(new ProviderWorker(), $data, $worker);

        return $worker->toDocumentArray();
    }

    public function delete(string $providerId, string $id): bool
    {
        $worker = ProviderWorker::query()
            ->where('id', $id)
            ->where('providerId', $providerId)
            ->first();

        if (! $worker) {
            return false;
        }

        $worker->delete();

        return true;
    }

    public function uploadImage(string $providerId, string $id, $file): ?array
    {
        $worker = ProviderWorker::query()
            ->where('id', $id)
            ->where('providerId', $providerId)
            ->first();

        if (! $worker) {
            return null;
        }

        $result = $this->storageService->upload($file, 'provider/workerImages', 'public');
        $worker->mergePayload(['profilePictureURL' => url($result['url'])]);
        $worker->save();

        return $worker->fresh()->toDocumentArray();
    }
}
