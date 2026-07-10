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
            ->when($vendorId, fn ($q) => $q->where('VendorId', $vendorId))
            ->when($productId, fn ($q) => $q->where('productId', $productId))
            ->when($customerId, fn ($q) => $q->where('CustomerId', $customerId))
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
        $data['id'] = $data['id'] ?? (string) Str::uuid();
        $data['CustomerId'] = $customerId;
        $data['createdAt'] = $data['createdAt'] ?? now();

        $review = ItemReview::query()->create($data);

        return $review->toDocumentArray();
    }

    public function createRating(string $customerId, array $data): array
    {
        $data['id'] = $data['id'] ?? (string) Str::uuid();
        $data['CustomerId'] = $customerId;
        $data['createdAt'] = $data['createdAt'] ?? now();

        $rating = Rating::query()->create($data);

        return $rating->toDocumentArray();
    }
}
