<?php

namespace App\Services\Vendor;

use App\Models\AppUser;
use App\Models\BookedTable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class VendorDineInService
{
    public function __construct(protected VendorProfileService $profileService)
    {
    }

    public function bookings(AppUser $user, ?string $tab = 'upcoming', int $perPage = 20): LengthAwarePaginator
    {
        $vendorId = $user->vendorID;
        if (! $vendorId) {
            throw ValidationException::withMessages(['vendorID' => ['Store not set up yet.']]);
        }

        $query = BookedTable::query()->where('vendorID', $vendorId);

        if ($tab === 'past' || $tab === 'history') {
            $query->where('date', '<', now());
        } else {
            $query->where('date', '>=', now());
        }

        return $query->orderByDesc('date')->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function show(AppUser $user, string $id): ?array
    {
        $booking = BookedTable::query()
            ->where('id', $id)
            ->where('vendorID', $user->vendorID)
            ->first();

        return $booking?->toDocumentArray();
    }

    public function accept(AppUser $user, string $id): array
    {
        $booking = $this->requireBooking($user, $id);
        $booking->update(['status' => VendorOrderService::STATUS_ACCEPTED]);

        return $booking->fresh()->toDocumentArray();
    }

    public function reject(AppUser $user, string $id, array $data = []): array
    {
        $booking = $this->requireBooking($user, $id);
        $payload = $booking->payload ?? [];
        $payload['reason'] = $data['reason'] ?? 'Rejected by vendor';
        $booking->update(['status' => VendorOrderService::STATUS_REJECTED, 'payload' => $payload]);

        return $booking->fresh()->toDocumentArray();
    }

    public function updateDineInConfig(AppUser $user, array $data): array
    {
        $store = $this->profileService->requireStore($user);
        $allowed = [
            'dine_in_active', 'openDineTime', 'closeDineTime', 'enabledDiveInFuture',
            'restaurantCost', 'restaurantMenuPhotos', 'specialDiscount', 'specialDiscountEnable',
        ];
        $update = array_intersect_key($data, array_flip($allowed));
        if ($update !== []) {
            $store->update($update);
        }

        return $store->fresh()->toDocumentArray();
    }

    protected function requireBooking(AppUser $user, string $id): BookedTable
    {
        $booking = BookedTable::query()->where('id', $id)->where('vendorID', $user->vendorID)->first();
        if (! $booking) {
            throw ValidationException::withMessages(['id' => ['Booking not found.']]);
        }

        return $booking;
    }
}
