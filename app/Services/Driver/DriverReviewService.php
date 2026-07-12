<?php

namespace App\Services\Driver;

use App\Models\AppUser;
use App\Models\ItemReview;
use App\Models\Rating;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

    public function store(AppUser $driver, array $data): array
    {
        $customerId = $data['customerId'] ?? $data['authorID'] ?? null;

        if (! $customerId) {
            throw ValidationException::withMessages(['customerId' => ['Customer id is required.']]);
        }

        $existing = ItemReview::query()
            ->where('orderid', $data['orderId'] ?? $data['orderid'])
            ->first();

        $payload = [
            'CustomerId' => $customerId,
            'driverId' => $driver->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ];

        if ($existing) {
            $existing->update(['payload' => array_merge($existing->payload ?? [], $payload)]);
            $review = $existing->fresh();
        } else {
            $review = ItemReview::query()->create([
                'id' => $data['id'] ?? (string) Str::uuid(),
                'orderid' => $data['orderId'] ?? $data['orderid'],
                'createdAt' => now(),
                'payload' => $payload,
            ]);
        }

        $customer = AppUser::query()->find($customerId);

        if ($customer) {
            $count = (int) ($customer->reviewsCount ?? 0);
            $sum = (int) ($customer->reviewsSum ?? 0);

            if ($existing) {
                $oldRating = (int) data_get($existing->payload, 'rating', 0);
                $count = max(0, $count - 1);
                $sum = max(0, $sum - $oldRating);
            }

            $customer->update([
                'reviewsCount' => $count + 1,
                'reviewsSum' => $sum + (int) $data['rating'],
            ]);
        }

        return $review->toDocumentArray();
    }
}
