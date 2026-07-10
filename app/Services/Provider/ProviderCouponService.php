<?php

namespace App\Services\Provider;

use App\Models\AppUser;
use App\Models\ProviderCoupon;
use App\Support\CatalogEntityWriter;
use App\Services\Storage\FileStorageService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProviderCouponService
{
    public function __construct(protected FileStorageService $storageService)
    {
    }

    public function list(string $providerId, int $perPage = 20): LengthAwarePaginator
    {
        return ProviderCoupon::query()
            ->where('providerId', $providerId)
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function show(string $providerId, string $id): ?array
    {
        $coupon = ProviderCoupon::query()
            ->where('id', $id)
            ->where('providerId', $providerId)
            ->first();

        return $coupon?->toDocumentArray();
    }

    public function create(AppUser $provider, array $data): array
    {
        $data['providerId'] = $provider->id;
        $data['sectionId'] = $data['sectionId'] ?? $provider->sectionId ?? $provider->section_id;
        $data['createdAt'] = $data['createdAt'] ?? now();
        $data['isEnabled'] = $data['isEnabled'] ?? true;
        $data['isPublic'] = $data['isPublic'] ?? true;

        $coupon = CatalogEntityWriter::write(new ProviderCoupon(), $data);

        return $coupon->toDocumentArray();
    }

    public function update(string $providerId, string $id, array $data): ?array
    {
        $coupon = ProviderCoupon::query()
            ->where('id', $id)
            ->where('providerId', $providerId)
            ->first();

        if (! $coupon) {
            return null;
        }

        unset($data['providerId'], $data['id']);
        $coupon = CatalogEntityWriter::write(new ProviderCoupon(), $data, $coupon);

        return $coupon->toDocumentArray();
    }

    public function delete(string $providerId, string $id): bool
    {
        $coupon = ProviderCoupon::query()
            ->where('id', $id)
            ->where('providerId', $providerId)
            ->first();

        if (! $coupon) {
            return false;
        }

        $coupon->delete();

        return true;
    }

    public function uploadImage(string $providerId, string $id, $file): ?array
    {
        $coupon = ProviderCoupon::query()
            ->where('id', $id)
            ->where('providerId', $providerId)
            ->first();

        if (! $coupon) {
            return null;
        }

        $result = $this->storageService->upload($file, 'provider/couponImages', 'public');
        $coupon->mergePayload(['image' => url($result['url'])]);
        $coupon->save();

        return $coupon->fresh()->toDocumentArray();
    }
}
