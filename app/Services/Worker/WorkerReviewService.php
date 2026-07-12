<?php

namespace App\Services\Worker;

use App\Models\AppUser;
use App\Models\ItemReview;
use App\Models\Rating;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WorkerReviewService
{
    public function __construct(protected WorkerProfileService $profileService)
    {
    }

    public function list(AppUser $user, int $perPage = 20): LengthAwarePaginator
    {
        $worker = $this->profileService->getWorkerOrFail($user);
        $workerId = $worker->id;

        return ItemReview::query()
            ->where(function ($q) use ($workerId) {
                $q->where('payload->driverId', $workerId)
                    ->orWhere('payload->workerId', $workerId)
                    ->orWhere('VendorId', $workerId);
            })
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function forOrder(AppUser $user, string $orderId): ?array
    {
        $worker = $this->profileService->getWorkerOrFail($user);
        $workerId = $worker->id;

        $review = ItemReview::query()
            ->where('orderid', $orderId)
            ->where(function ($q) use ($workerId) {
                $q->where('payload->driverId', $workerId)
                    ->orWhere('payload->workerId', $workerId)
                    ->orWhere('VendorId', $workerId);
            })
            ->first();

        return $review?->toDocumentArray();
    }

    public function ratings(AppUser $user, int $perPage = 20): LengthAwarePaginator
    {
        $worker = $this->profileService->getWorkerOrFail($user);
        $workerId = $worker->id;

        return Rating::query()
            ->where(function ($q) use ($workerId) {
                $q->where('payload->driverId', $workerId)
                    ->orWhere('payload->workerId', $workerId);
            })
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }
}
