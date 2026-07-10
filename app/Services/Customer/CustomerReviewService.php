<?php

namespace App\Services\Customer;

use App\Models\ItemReview;
use App\Models\Rating;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CustomerReviewService
{
    public function list(string $customerId, ?string $orderId = null, ?string $vendorId = null, ?string $productId = null, int $perPage = 20): LengthAwarePaginator
    {
        return ItemReview::query()
            ->when($orderId, fn ($q) => $q->where('orderid', $orderId))
            ->when($vendorId, fn ($q) => $q->where(function ($q) use ($vendorId) {
                $q->where('VendorId', $vendorId)->orWhere('payload->VendorId', $vendorId);
            }))
            ->when($productId, fn ($q) => $q->where(function ($q) use ($productId) {
                $q->where('productId', $productId)->orWhere('payload->productId', $productId);
            }))
            ->when($customerId, fn ($q) => $q->where(function ($q) use ($customerId) {
                $q->where('payload->CustomerId', $customerId)
                    ->orWhere('payload->customerId', $customerId);
            }))
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function vendorReviews(string $vendorId, int $perPage = 20): LengthAwarePaginator
    {
        return ItemReview::query()
            ->where('VendorId', $vendorId)
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function serviceReviews(string $serviceId, int $perPage = 20): LengthAwarePaginator
    {
        return ItemReview::query()
            ->where('productId', $serviceId)
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function create(string $customerId, array $data): array
    {
        $payload = array_filter([
            'CustomerId' => $customerId,
            'comment' => $data['comment'] ?? null,
            'rating' => $data['rating'] ?? null,
            'photos' => $data['photos'] ?? null,
        ], static fn ($value) => $value !== null);

        $review = ItemReview::query()->create([
            'id' => $data['id'] ?? (string) Str::uuid(),
            'orderid' => $data['orderid'] ?? $data['orderId'] ?? null,
            'VendorId' => $data['VendorId'] ?? $data['vendorId'] ?? null,
            'productId' => $data['productId'] ?? null,
            'createdAt' => $data['createdAt'] ?? now(),
            'payload' => $payload,
        ]);

        return $review->toDocumentArray();
    }

    public function createRating(string $customerId, array $data): array
    {
        $payload = array_filter([
            'CustomerId' => $customerId,
            'rating' => $data['rating'] ?? null,
            'comment' => $data['comment'] ?? null,
        ], static fn ($value) => $value !== null);

        $rating = Rating::query()->create([
            'id' => $data['id'] ?? (string) Str::uuid(),
            'orderid' => $data['orderid'] ?? $data['orderId'] ?? null,
            'createdAt' => $data['createdAt'] ?? now(),
            'payload' => $payload,
        ]);

        return $rating->toDocumentArray();
    }
}
