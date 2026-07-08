<?php

namespace App\Services\Provider;

use App\Models\AppUser;
use App\Models\DriverPayout;
use App\Models\Payout;
use App\Models\Wallet;
use App\Models\WithdrawMethod;
use App\Support\CatalogEntityWriter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProviderWalletService
{
    public function balance(AppUser $user): array
    {
        return [
            'wallet_amount' => (float) ($user->wallet_amount ?? 0),
        ];
    }

    public function transactions(string $providerId, int $perPage = 20): LengthAwarePaginator
    {
        return Wallet::query()
            ->where('user_id', $providerId)
            ->orderByDesc('date')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function earnings(string $providerId): array
    {
        $income = Wallet::query()
            ->where('user_id', $providerId)
            ->where('isTopUp', true)
            ->sum('amount');

        $withdrawn = Wallet::query()
            ->where('user_id', $providerId)
            ->where(function ($q) {
                $q->where('isTopUp', false)->orWhereNull('isTopUp');
            })
            ->sum('amount');

        $user = AppUser::query()->find($providerId);

        return [
            'wallet_amount' => (float) ($user->wallet_amount ?? 0),
            'total_earnings' => (float) $income,
            'total_withdrawn' => (float) $withdrawn,
        ];
    }

    public function withdraw(AppUser $provider, array $data): array
    {
        $amount = (float) $data['amount'];
        $balance = (float) ($provider->wallet_amount ?? 0);

        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => ['Amount must be greater than zero.']]);
        }

        if ($amount > $balance) {
            throw ValidationException::withMessages(['amount' => ['Insufficient wallet balance.']]);
        }

        $provider->decrement('wallet_amount', $amount);

        Wallet::query()->create([
            'id' => (string) Str::uuid(),
            'user_id' => $provider->id,
            'amount' => $amount,
            'isTopUp' => false,
            'payment_method' => $data['withdrawMethod'] ?? 'bank',
            'payment_status' => 'pending',
            'note' => $data['note'] ?? 'Payout request',
            'transactionUser' => 'provider',
            'serviceType' => 'ondemand-service',
            'date' => now(),
        ]);

        $payout = Payout::query()->create([
            'id' => (string) Str::uuid(),
            'vendorID' => $provider->id,
            'amount' => $amount,
            'note' => $data['note'] ?? 'Payout request',
            'paymentStatus' => 'Pending',
            'paidDate' => now(),
            'role' => 'provider',
            'withdrawMethod' => $data['withdrawMethod'] ?? null,
            'payload' => [
                'bankDetails' => $provider->userBankDetails ?? [],
            ],
        ]);

        DriverPayout::query()->create([
            'id' => (string) Str::uuid(),
            'driverID' => $provider->id,
            'vendorID' => $provider->id,
            'amount' => $amount,
            'note' => $data['note'] ?? 'Payout request',
            'paymentStatus' => 'Pending',
            'paidDate' => now(),
            'role' => 'provider',
            'withdrawMethod' => $data['withdrawMethod'] ?? null,
        ]);

        return [
            'wallet_amount' => (float) $provider->fresh()->wallet_amount,
            'payout' => $payout->toDocumentArray(),
        ];
    }

    public function payoutHistory(string $providerId, int $perPage = 20): LengthAwarePaginator
    {
        return Payout::query()
            ->where('vendorID', $providerId)
            ->where('role', 'provider')
            ->orderByDesc('paidDate')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function getWithdrawMethod(string $providerId): ?array
    {
        $method = WithdrawMethod::query()->where('userId', $providerId)->first();

        return $method?->toDocumentArray();
    }

    public function saveWithdrawMethod(string $providerId, array $data): array
    {
        $existing = WithdrawMethod::query()->where('userId', $providerId)->first();

        $payload = [
            'userId' => $providerId,
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
