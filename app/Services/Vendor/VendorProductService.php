<?php

namespace App\Services\Vendor;

use App\Models\AppUser;
use App\Models\FavoriteItem;
use App\Models\VendorProduct;
use App\Services\Storage\FileStorageService;
use App\Support\CatalogEntityWriter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VendorProductService
{
    public function __construct(protected FileStorageService $storageService)
    {
    }

    public function list(AppUser $user, int $perPage = 20): LengthAwarePaginator
    {
        return VendorProduct::query()
            ->where('vendorID', $user->vendorID)
            ->orderBy('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function show(AppUser $user, string $id): ?array
    {
        $product = VendorProduct::query()
            ->where('id', $id)
            ->where('vendorID', $user->vendorID)
            ->first();

        return $product?->toDocumentArray();
    }

    public function create(AppUser $user, array $data): array
    {
        $data['vendorID'] = $user->vendorID;
        $data['section_id'] = $data['section_id'] ?? $data['sectionId'] ?? $user->section_id ?? $user->sectionId;
        $data['createdAt'] = $data['createdAt'] ?? now();
        $data['publish'] = $data['publish'] ?? true;

        $product = CatalogEntityWriter::write(new VendorProduct(), $data);

        return $product->toDocumentArray();
    }

    public function update(AppUser $user, string $id, array $data): ?array
    {
        $product = VendorProduct::query()
            ->where('id', $id)
            ->where('vendorID', $user->vendorID)
            ->first();

        if (! $product) {
            return null;
        }

        unset($data['vendorID'], $data['id']);
        $product = CatalogEntityWriter::write(new VendorProduct(), $data, $product);

        return $product->toDocumentArray();
    }

    public function delete(AppUser $user, string $id): bool
    {
        $product = VendorProduct::query()
            ->where('id', $id)
            ->where('vendorID', $user->vendorID)
            ->first();

        if (! $product) {
            return false;
        }

        FavoriteItem::query()->where('product_id', $id)->delete();
        $product->delete();

        return true;
    }

    public function uploadImages(AppUser $user, string $id, array $files): ?array
    {
        $product = VendorProduct::query()
            ->where('id', $id)
            ->where('vendorID', $user->vendorID)
            ->first();

        if (! $product) {
            return null;
        }

        $photos = $product->photos ?? [];
        if (! is_array($photos)) {
            $photos = [];
        }

        foreach ($files as $file) {
            $result = $this->storageService->upload($file, 'images/' . $user->id, 'public');
            $photos[] = url($result['url']);
        }

        $product->update(['photos' => $photos]);

        return $product->fresh()->toDocumentArray();
    }
}
