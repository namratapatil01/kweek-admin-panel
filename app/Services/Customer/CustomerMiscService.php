<?php

namespace App\Services\Customer;

use App\Models\AppNotification;
use App\Models\Complaint;
use App\Models\GiftCard;
use App\Models\GiftPurchase;
use App\Models\Referral;
use App\Models\Sos;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CustomerMiscService
{
    public function notifications(?string $role = 'customer', int $perPage = 20): LengthAwarePaginator
    {
        return AppNotification::query()
            ->when($role, fn ($q) => $q->where('role', $role))
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function referral(string $customerId): ?array
    {
        $referral = Referral::query()->find($customerId);

        return $referral?->toDocumentArray();
    }

    public function giftCards(int $perPage = 20): LengthAwarePaginator
    {
        return GiftCard::query()
            ->where('isEnable', true)
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function purchaseGiftCard(string $customerId, array $data): array
    {
        $data['id'] = $data['id'] ?? (string) Str::uuid();
        $data['userid'] = $customerId;

        $purchase = GiftPurchase::query()->create($data);

        return $purchase->toDocumentArray();
    }

    public function createComplaint(string $customerId, array $data): array
    {
        $data['id'] = $data['id'] ?? (string) Str::uuid();
        $data['customerId'] = $customerId;
        $data['createdAt'] = $data['createdAt'] ?? now();

        $complaint = Complaint::query()->create($data);

        return $complaint->toDocumentArray();
    }

    public function createSos(string $customerId, array $data): array
    {
        $data['id'] = $data['id'] ?? (string) Str::uuid();
        $data['customerId'] = $customerId;

        $sos = Sos::query()->create($data);

        return $sos->toDocumentArray();
    }
}
