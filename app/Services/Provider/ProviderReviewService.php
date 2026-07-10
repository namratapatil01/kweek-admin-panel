<?php

namespace App\Services\Provider;

use App\Models\ItemReview;
use App\Models\Rating;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProviderReviewService
{
    public function list(string $providerId, int $perPage = 20): LengthAwarePaginator
    {
        return ItemReview::query()
            ->where(function ($q) use ($providerId) {
                $q->where('VendorId', $providerId)
                    ->orWhere('payload->providerId', $providerId)
                    ->orWhere('payload->VendorId', $providerId);
            })
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function forOrder(string $providerId, string $orderId): ?array
    {
        $review = ItemReview::query()
            ->where('orderid', $orderId)
            ->where(function ($q) use ($providerId) {
                $q->where('VendorId', $providerId)
                    ->orWhere('payload->providerId', $providerId)
                    ->orWhere('payload->VendorId', $providerId);
            })
            ->first();

        return $review?->toDocumentArray();
    }

    public function ratings(string $providerId, int $perPage = 20): LengthAwarePaginator
    {
        return Rating::query()
            ->where(function ($q) use ($providerId) {
                $q->where('payload->providerId', $providerId)
                    ->orWhere('payload->VendorId', $providerId);
            })
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }
}
