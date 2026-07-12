<?php

namespace App\Services\Customer;

use App\Models\Cashback;
use App\Models\CashbackRedeem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CustomerCashbackService
{
    public function list(int $perPage = 20): LengthAwarePaginator
    {
        return Cashback::query()
            ->where('isEnabled', true)
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function redeemed(string $customerId, ?string $cashbackId = null, int $perPage = 20): LengthAwarePaginator
    {
        return CashbackRedeem::query()
            ->where('userId', $customerId)
            ->when($cashbackId, fn ($q) => $q->where('cashbackId', $cashbackId))
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function redeem(string $customerId, array $data): array
    {
        $payload = array_merge($data, [
            'id' => $data['id'] ?? (string) Str::uuid(),
            'userId' => $customerId,
            'createdAt' => $data['createdAt'] ?? now(),
        ]);

        $redeem = CashbackRedeem::query()->create($payload);

        return $redeem->toDocumentArray();
    }
}
