<?php

namespace App\Services\Driver;

use App\Models\AppUser;
use App\Models\DriverPayout;
use App\Models\Wallet;
use App\Models\WithdrawMethod;
use App\Support\CatalogEntityWriter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DriverWalletService
{
    public function balance(AppUser $driver): array
    {
        return [
            'wallet_amount' => (float) ($driver->wallet_amount ?? 0),
        ];
    }

    public function transactions(string $driverId, int $perPage = 20): LengthAwarePaginator
    {
        return Wallet::query()
            ->where('user_id', $driverId)
            ->orderByDesc('date')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function earnings(string $driverId): array
    {
        $income = Wallet::query()
            ->where('user_id', $driverId)
            ->where('isTopUp', true)
            ->sum('amount');

        $withdrawn = Wallet::query()
            ->where('user_id', $driverId)
            ->where(function ($q) {
                $q->where('isTopUp', false)->orWhereNull('isTopUp');
            })
            ->sum('amount');

        $user = AppUser::query()->find($driverId);

        return [
            'wallet_amount' => (float) ($user->wallet_amount ?? 0),
            'total_earnings' => (float) $income,
            'total_withdrawn' => (float) $withdrawn,
        ];
    }

    public function withdraw(AppUser $driver, array $data): array
    {
        $amount = (float) $data['amount'];
        $balance = (float) ($driver->wallet_amount ?? 0);

        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => ['Amount must be greater than zero.']]);
        }

        if ($amount > $balance) {
            throw ValidationException::withMessages(['amount' => ['Insufficient wallet balance.']]);
        }

        $driver->decrement('wallet_amount', $amount);

        Wallet::query()->create([
            'id' => (string) Str::uuid(),
            'user_id' => $driver->id,
            'amount' => $amount,
            'isTopUp' => false,
            'payment_method' => $data['withdrawMethod'] ?? 'bank',
            'payment_status' => 'pending',
            'note' => $data['note'] ?? 'Payout request',
            'transactionUser' => 'driver',
            'serviceType' => $driver->serviceType,
            'date' => now(),
        ]);

        $payout = DriverPayout::query()->create([
            'id' => (string) Str::uuid(),
            'driverID' => $driver->id,
            'vendorID' => $driver->vendorID,
            'amount' => $amount,
            'note' => $data['note'] ?? 'Payout request',
            'paymentStatus' => 'Pending',
            'paidDate' => now(),
            'role' => 'driver',
            'withdrawMethod' => $data['withdrawMethod'] ?? null,
            'payload' => [
                'bankDetails' => $driver->userBankDetails ?? [],
            ],
        ]);

        return [
            'wallet_amount' => (float) $driver->fresh()->wallet_amount,
            'payout' => $payout->toDocumentArray(),
        ];
    }

    public function payoutHistory(string $driverId, int $perPage = 20): LengthAwarePaginator
    {
        return DriverPayout::query()
            ->where('driverID', $driverId)
            ->where('role', 'driver')
            ->orderByDesc('paidDate')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function getWithdrawMethod(string $driverId): ?array
    {
        $method = WithdrawMethod::query()->where('userId', $driverId)->first();

        return $method?->toDocumentArray();
    }

    public function saveWithdrawMethod(string $driverId, array $data): array
    {
        $existing = WithdrawMethod::query()->where('userId', $driverId)->first();

        $payload = [
            'userId' => $driverId,
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
