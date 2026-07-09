<?php

namespace App\Services\Vendor;

use App\Models\AppUser;
use App\Models\Story;
use App\Services\Storage\FileStorageService;
use App\Support\CatalogEntityWriter;

class VendorStoryService
{
    public function __construct(protected FileStorageService $storageService)
    {
    }

    public function show(AppUser $user): ?array
    {
        if (! $user->vendorID) {
            return null;
        }

        return Story::query()->find($user->vendorID)?->toDocumentArray();
    }

    public function save(AppUser $user, array $data): array
    {
        $vendorId = $user->vendorID;
        $existing = Story::query()->find($vendorId);

        $payload = array_merge($data, [
            'id' => $vendorId,
            'vendorID' => $vendorId,
        ]);

        if ($existing) {
            $story = CatalogEntityWriter::write(new Story(), $payload, $existing);
        } else {
            $story = CatalogEntityWriter::write(new Story(), $payload);
        }

        return $story->toDocumentArray();
    }

    public function delete(AppUser $user): bool
    {
        if (! $user->vendorID) {
            return false;
        }

        return (bool) Story::query()->where('id', $user->vendorID)->delete();
    }

    public function uploadMedia(AppUser $user, $file, string $mediaType = 'image'): array
    {
        $directory = $mediaType === 'video' ? 'Story' : 'Story/images';
        $result = $this->storageService->upload($file, $directory, 'public');

        return ['url' => url($result['url']), 'mime' => $result['mime_type'], 'path' => $result['path']];
    }
}
