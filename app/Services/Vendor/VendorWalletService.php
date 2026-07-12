<?php

namespace App\Services\Vendor;

use App\Models\AppUser;
use App\Models\Payout;
use App\Models\Wallet;
use App\Models\WithdrawMethod;
use App\Support\CatalogEntityWriter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VendorWalletService
{
    public function balance(AppUser $user): array
    {
        return ['wallet_amount' => (float) ($user->wallet_amount ?? 0)];
    }

    public function transactions(string $userId, int $perPage = 20): LengthAwarePaginator
    {
        return Wallet::query()
            ->where('user_id', $userId)
            ->orderByDesc('date')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function earnings(string $userId): array
    {
        $income = Wallet::query()->where('user_id', $userId)->where('isTopUp', true)->sum('amount');
        $withdrawn = Wallet::query()->where('user_id', $userId)
            ->where(function ($q) {
                $q->where('isTopUp', false)->orWhereNull('isTopUp');
            })
            ->sum('amount');
        $user = AppUser::query()->find($userId);

        return [
            'wallet_amount' => (float) ($user->wallet_amount ?? 0),
            'total_earnings' => (float) $income,
            'total_withdrawn' => (float) $withdrawn,
        ];
    }

    public function withdraw(AppUser $user, array $data): array
    {
        $amount = (float) $data['amount'];
        $balance = (float) ($user->wallet_amount ?? 0);

        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => ['Amount must be greater than zero.']]);
        }
        if ($amount > $balance) {
            throw ValidationException::withMessages(['amount' => ['Insufficient wallet balance.']]);
        }

        $user->decrement('wallet_amount', $amount);

        Wallet::query()->create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'amount' => $amount,
            'isTopUp' => false,
            'payment_method' => $data['withdrawMethod'] ?? 'bank',
            'payment_status' => 'pending',
            'note' => $data['note'] ?? 'Payout request',
            'transactionUser' => 'vendor',
            'date' => now(),
        ]);

        $payout = Payout::query()->create([
            'id' => (string) Str::uuid(),
            'vendorID' => $user->vendorID ?? $user->id,
            'amount' => $amount,
            'note' => $data['note'] ?? 'Payout request',
            'paymentStatus' => 'Pending',
            'paidDate' => now(),
            'role' => 'vendor',
            'withdrawMethod' => $data['withdrawMethod'] ?? null,
            'payload' => ['bankDetails' => $user->userBankDetails ?? []],
        ]);

        return [
            'wallet_amount' => (float) $user->fresh()->wallet_amount,
            'payout' => $payout->toDocumentArray(),
        ];
    }

    public function payoutHistory(string $vendorId, int $perPage = 20): LengthAwarePaginator
    {
        return Payout::query()
            ->where('vendorID', $vendorId)
            ->where('role', 'vendor')
            ->orderByDesc('paidDate')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function getWithdrawMethod(string $userId): ?array
    {
        return WithdrawMethod::query()->where('userId', $userId)->first()?->toDocumentArray();
    }

    public function saveWithdrawMethod(string $userId, array $data): array
    {
        $existing = WithdrawMethod::query()->where('userId', $userId)->first();
        $payload = [
            'userId' => $userId,
            'flutterwave' => $data['flutterwave'] ?? null,
            'stripe' => $data['stripe'] ?? null,
            'razorpay' => $data['razorpay'] ?? null,
            'paypal' => $data['paypal'] ?? null,
        ];

        if ($existing) {
            $method = CatalogEntityWriter::write(new WithdrawMethod(), $payload, $existing);
        } else {
            $payload['id'] = (string) Str::uuid();
            $payload['createdAt'] = now();
            $method = CatalogEntityWriter::write(new WithdrawMethod(), $payload);
        }

        return $method->toDocumentArray();
    }
}
