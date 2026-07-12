<?php

namespace App\Services\Provider;

use App\Models\AppUser;
use App\Models\ProviderCategory;
use App\Models\ProviderService;
use App\Models\Section;
use App\Support\CatalogEntityWriter;
use App\Services\Storage\FileStorageService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
class ProviderServiceService
{
    public function __construct(protected FileStorageService $storageService)
    {
    }

    public function list(string $providerId, int $perPage = 20): LengthAwarePaginator
    {
        return ProviderService::query()
            ->where('payload->author', $providerId)
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function show(string $providerId, string $id): ?array
    {
        $service = ProviderService::query()
            ->where('id', $id)
            ->where('payload->author', $providerId)
            ->first();

        return $service?->toDocumentArray();
    }

    public function create(AppUser $provider, array $data): array
    {
        $data['author'] = $provider->id;
        $data['authorName'] = trim(($provider->firstName ?? '') . ' ' . ($provider->lastName ?? ''));
        $data['authorProfilePic'] = $provider->profilePictureURL;
        $data['sectionId'] = $data['sectionId'] ?? $provider->sectionId ?? $provider->section_id;
        $data['createdAt'] = $data['createdAt'] ?? now();
        $data['reviewsCount'] = $data['reviewsCount'] ?? 0;
        $data['reviewsSum'] = $data['reviewsSum'] ?? 0;
        $data['publish'] = $data['publish'] ?? true;

        if (isset($data['latitude'], $data['longitude'])) {
            $data['g'] = $data['g'] ?? [
                'geohash' => null,
                'geopoint' => [
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                ],
            ];
        }

        $service = CatalogEntityWriter::write(new ProviderService(), $data);

        return $service->toDocumentArray();
    }

    public function update(string $providerId, string $id, array $data): ?array
    {
        $service = ProviderService::query()
            ->where('id', $id)
            ->where('payload->author', $providerId)
            ->first();

        if (! $service) {
            return null;
        }

        unset($data['author'], $data['id']);
        $service = CatalogEntityWriter::write(new ProviderService(), $data, $service);

        return $service->toDocumentArray();
    }

    public function delete(string $providerId, string $id): bool
    {
        $service = ProviderService::query()
            ->where('id', $id)
            ->where('payload->author', $providerId)
            ->first();

        if (! $service) {
            return false;
        }

        $service->delete();

        return true;
    }

    public function uploadImages(string $providerId, string $id, array $files): ?array
    {
        $service = ProviderService::query()
            ->where('id', $id)
            ->where('payload->author', $providerId)
            ->first();

        if (! $service) {
            return null;
        }

        $photos = $service->payload['photos'] ?? [];
        if (! is_array($photos)) {
            $photos = [];
        }

        foreach ($files as $file) {
            $result = $this->storageService->upload($file, 'provider/serviceImages', 'public');
            $photos[] = url($result['url']);
        }

        $service->mergePayload(['photos' => $photos]);
        $service->save();

        return $service->fresh()->toDocumentArray();
    }

    public function categories(?string $sectionId = null, ?string $parentCategoryId = null, int $perPage = 50): LengthAwarePaginator
    {
        $query = ProviderCategory::query()
            ->where(function ($q) {
                $q->where('publish', true)->orWhere('isActive', true)->orWhereNull('publish');
            });

        if ($sectionId) {
            $query->where('sectionId', $sectionId);
        }

        if ($parentCategoryId) {
            $query->where('parentCategoryId', $parentCategoryId)
                ->where(function ($q) {
                    $q->where('payload->level', 1)->orWhereNull('payload->level');
                });
        } else {
            $query->where(function ($q) {
                $q->whereNull('parentCategoryId')
                    ->orWhere('parentCategoryId', '')
                    ->orWhere('payload->level', 0);
            });
        }

        return $query->paginate($perPage)->through(fn ($item) => $item->toDocumentArray());
    }

    public function sections(int $perPage = 50): LengthAwarePaginator
    {
        return Section::query()
            ->where('isActive', true)
            ->where(function ($q) {
                $q->where('serviceTypeFlag', 'ondemand-service')
                    ->orWhere('serviceType', 'ondemand-service');
            })
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }
}
