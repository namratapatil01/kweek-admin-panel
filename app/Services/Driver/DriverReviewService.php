<?php

namespace App\Services\Driver;

use App\Models\AppUser;
use App\Models\ItemReview;
use App\Models\Rating;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DriverReviewService
{
    public function list(AppUser $driver, int $perPage = 20): LengthAwarePaginator
    {
        $driverId = $driver->id;

        return ItemReview::query()
            ->where(function ($q) use ($driverId) {
                $q->where('payload->driverId', $driverId)
                    ->orWhere('payload->driverID', $driverId)
                    ->orWhere('VendorId', $driverId);
            })
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function forOrder(AppUser $driver, string $orderId): ?array
    {
        $driverId = $driver->id;

        $review = ItemReview::query()
            ->where('orderid', $orderId)
            ->where(function ($q) use ($driverId) {
                $q->where('payload->driverId', $driverId)
                    ->orWhere('payload->driverID', $driverId)
                    ->orWhere('VendorId', $driverId);
            })
            ->first();

        return $review?->toDocumentArray();
    }

    public function ratings(AppUser $driver, int $perPage = 20): LengthAwarePaginator
    {
        $driverId = $driver->id;

        return Rating::query()
            ->where(function ($q) use ($driverId) {
                $q->where('payload->driverId', $driverId)
                    ->orWhere('payload->driverID', $driverId);
            })
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }
}
