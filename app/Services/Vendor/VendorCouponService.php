<?php

namespace App\Services\Vendor;

use App\Models\AppUser;
use App\Models\Coupon;
use App\Services\Storage\FileStorageService;
use App\Support\CatalogEntityWriter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VendorCouponService
{
    public function __construct(protected FileStorageService $storageService)
    {
    }

    public function list(AppUser $user, int $perPage = 20): LengthAwarePaginator
    {
        return Coupon::query()
            ->where('vendorID', $user->vendorID)
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function show(AppUser $user, string $id): ?array
    {
        $coupon = Coupon::query()->where('id', $id)->where('vendorID', $user->vendorID)->first();

        return $coupon?->toDocumentArray();
    }

    public function create(AppUser $user, array $data): array
    {
        $data['vendorID'] = $user->vendorID;
        $data['section_id'] = $data['section_id'] ?? $data['sectionId'] ?? $user->section_id ?? $user->sectionId;
        $data['createdAt'] = $data['createdAt'] ?? now();
        $data['isEnabled'] = $data['isEnabled'] ?? true;
        $data['isPublic'] = $data['isPublic'] ?? true;

        $coupon = CatalogEntityWriter::write(new Coupon(), $data);

        return $coupon->toDocumentArray();
    }

    public function update(AppUser $user, string $id, array $data): ?array
    {
        $coupon = Coupon::query()->where('id', $id)->where('vendorID', $user->vendorID)->first();
        if (! $coupon) {
            return null;
        }

        unset($data['vendorID'], $data['id']);
        $coupon = CatalogEntityWriter::write(new Coupon(), $data, $coupon);

        return $coupon->toDocumentArray();
    }

    public function delete(AppUser $user, string $id): bool
    {
        $coupon = Coupon::query()->where('id', $id)->where('vendorID', $user->vendorID)->first();
        if (! $coupon) {
            return false;
        }

        $coupon->delete();

        return true;
    }

    public function uploadImage(AppUser $user, string $id, $file): ?array
    {
        $coupon = Coupon::query()->where('id', $id)->where('vendorID', $user->vendorID)->first();
        if (! $coupon) {
            return null;
        }

        $result = $this->storageService->upload($file, 'profileImage/' . $user->id, 'public');
        $coupon->mergePayload(['image' => url($result['url'])]);
        $coupon->save();

        return $coupon->fresh()->toDocumentArray();
    }
}
