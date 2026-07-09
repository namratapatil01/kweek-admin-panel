<?php

namespace App\Services\Vendor;

use App\Models\AppUser;
use App\Models\ItemReview;
use App\Models\Rating;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VendorReviewService
{
    public function list(AppUser $user, int $perPage = 20): LengthAwarePaginator
    {
        $vendorId = $user->vendorID;

        return ItemReview::query()
            ->where(fn ($q) => $q->where('VendorId', $vendorId)->orWhere('payload->VendorId', $vendorId))
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function forOrder(AppUser $user, string $orderId, ?string $productId = null): ?array
    {
        $query = ItemReview::query()
            ->where('orderid', $orderId)
            ->where(fn ($q) => $q->where('VendorId', $user->vendorID)->orWhere('payload->VendorId', $user->vendorID));

        if ($productId) {
            $query->where(fn ($q) => $q->where('productId', $productId)->orWhere('payload->productId', $productId));
        }

        return $query->first()?->toDocumentArray();
    }

    public function ratings(AppUser $user, int $perPage = 20): LengthAwarePaginator
    {
        return Rating::query()
            ->where(fn ($q) => $q->where('payload->VendorId', $user->vendorID)->orWhere('VendorId', $user->vendorID))
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }
}
