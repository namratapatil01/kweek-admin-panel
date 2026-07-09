<?php

namespace App\Services\Vendor;

use App\Models\Advertisement;
use App\Models\AppUser;
use App\Services\Storage\FileStorageService;
use App\Support\CatalogEntityWriter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VendorAdvertisementService
{
    public function __construct(protected FileStorageService $storageService)
    {
    }

    public function list(AppUser $user, int $perPage = 20): LengthAwarePaginator
    {
        return Advertisement::query()
            ->where('vendorId', $user->vendorID)
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function show(AppUser $user, string $id): ?array
    {
        return Advertisement::query()->where('id', $id)->where('vendorId', $user->vendorID)->first()?->toDocumentArray();
    }

    public function create(AppUser $user, array $data): array
    {
        $data['vendorId'] = $user->vendorID;
        $data['createdAt'] = $data['createdAt'] ?? now();
        $data['status'] = $data['status'] ?? 'pending';

        return CatalogEntityWriter::write(new Advertisement(), $data)->toDocumentArray();
    }

    public function update(AppUser $user, string $id, array $data): ?array
    {
        $ad = Advertisement::query()->where('id', $id)->where('vendorId', $user->vendorID)->first();
        if (! $ad) {
            return null;
        }

        unset($data['vendorId'], $data['id']);

        return CatalogEntityWriter::write(new Advertisement(), $data, $ad)->toDocumentArray();
    }

    public function delete(AppUser $user, string $id): bool
    {
        $ad = Advertisement::query()->where('id', $id)->where('vendorId', $user->vendorID)->first();
        if (! $ad) {
            return false;
        }

        $ad->delete();

        return true;
    }

    public function uploadMedia(AppUser $user, string $id, $file, string $type = 'profile'): ?array
    {
        $ad = Advertisement::query()->where('id', $id)->where('vendorId', $user->vendorID)->first();
        if (! $ad) {
            return null;
        }

        $dir = match ($type) {
            'cover' => 'advCoverImage',
            'video' => 'advVideo',
            default => 'advProfileImage',
        };

        $result = $this->storageService->upload($file, $dir . '/' . $id, 'public');
        $url = url($result['url']);

        $field = match ($type) {
            'cover' => 'coverImage',
            'video' => 'videoThumbnail',
            default => 'profileImage',
        };

        $ad->mergePayload([$field => $url]);
        $ad->save();

        return $ad->fresh()->toDocumentArray();
    }
}
