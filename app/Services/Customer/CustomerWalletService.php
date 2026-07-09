<?php

namespace App\Services\Customer;

use App\Models\AppUser;
use App\Models\Wallet;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CustomerWalletService
{
    public function transactions(string $customerId, int $perPage = 20): LengthAwarePaginator
    {
        return Wallet::query()
            ->where('user_id', $customerId)
            ->orderByDesc('date')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function balance(AppUser $user): array
    {
        return [
            'wallet_amount' => (float) ($user->wallet_amount ?? 0),
        ];
    }

    public function topUp(string $customerId, array $data): array
    {
        $amount = (float) $data['amount'];

        $user = AppUser::query()->findOrFail($customerId);
        $user->increment('wallet_amount', $amount);

        $transaction = Wallet::query()->create([
            'id' => (string) Str::uuid(),
            'user_id' => $customerId,
            'amount' => $amount,
            'isTopUp' => true,
            'payment_method' => $data['payment_method'] ?? 'wallet',
            'payment_status' => $data['payment_status'] ?? 'success',
            'note' => $data['note'] ?? 'Wallet top-up',
            'date' => now(),
            'serviceType' => $data['serviceType'] ?? null,
            'order_id' => $data['order_id'] ?? null,
        ]);

        return [
            'wallet_amount' => (float) $user->fresh()->wallet_amount,
            'transaction' => $transaction->toDocumentArray(),
        ];
    }
}
