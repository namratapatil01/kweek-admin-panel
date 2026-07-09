<?php

namespace App\Services\Worker;

use App\Models\AppUser;
use App\Models\ProviderOrder;
use App\Models\Referral;
use App\Models\Section;
use App\Models\Wallet;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WorkerJobService
{
    public const STATUS_PLACED = 'Order Placed';
    public const STATUS_ACCEPTED = 'Order Accepted';
    public const STATUS_ASSIGNED = 'Order Assigned';
    public const STATUS_ONGOING = 'Order Ongoing';
    public const STATUS_COMPLETED = 'Order Completed';
    public const STATUS_REJECTED = 'Order Rejected';
    public const STATUS_CANCELLED = 'Order Cancelled';

    public function __construct(protected WorkerProfileService $profileService)
    {
    }

    public function list(AppUser $user, ?string $tab = null, int $perPage = 20): LengthAwarePaginator
    {
        $worker = $this->profileService->getWorkerOrFail($user);
        $query = ProviderOrder::query()->where('workerId', $worker->id);

        $tab = $tab ?: 'upcoming';

        match ($tab) {
            'upcoming', 'assigned' => $query->whereIn('status', [
                self::STATUS_ACCEPTED,
                self::STATUS_ASSIGNED,
                self::STATUS_ONGOING,
            ]),
            'today' => $query->whereIn('status', [
                self::STATUS_ACCEPTED,
                self::STATUS_ASSIGNED,
                self::STATUS_ONGOING,
            ])->where(function ($q) {
                $start = Carbon::today()->startOfDay();
                $end = Carbon::today()->endOfDay();
                $q->whereBetween('payload->newScheduleDateTime', [$start->toIso8601String(), $end->toIso8601String()])
                    ->orWhereBetween('createdAt', [$start, $end]);
            }),
            'ongoing' => $query->where('status', self::STATUS_ONGOING),
            'completed', 'history' => $query->where('status', self::STATUS_COMPLETED),
            'cancelled' => $query->whereIn('status', [self::STATUS_REJECTED, self::STATUS_CANCELLED]),
            default => null,
        };

        return $query->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function show(AppUser $user, string $id): ?array
    {
        $order = $this->findOwnedOrder($user, $id);

        return $order?->toDocumentArray();
    }

    /**
     * Optional accept — not in Flutter worker app, but useful if provider assigns as Accepted.
     */
    public function accept(AppUser $user, string $id): array
    {
        $order = $this->requireOrder($user, $id);

        if (! in_array($order->status, [self::STATUS_ACCEPTED, self::STATUS_ASSIGNED], true)) {
            throw ValidationException::withMessages([
                'status' => ['Only accepted/assigned jobs can be acknowledged.'],
            ]);
        }

        $order->update(['status' => self::STATUS_ASSIGNED]);

        return $order->fresh()->toDocumentArray();
    }

    /**
     * Optional reject — Flutter defines worker_rejected but does not use it.
     */
    public function reject(AppUser $user, string $id, array $data = []): array
    {
        $order = $this->requireOrder($user, $id);

        if (! in_array($order->status, [self::STATUS_ACCEPTED, self::STATUS_ASSIGNED], true)) {
            throw ValidationException::withMessages([
                'status' => ['Only accepted/assigned jobs can be rejected.'],
            ]);
        }

        $payload = $order->payload ?? [];
        $payload['reason'] = $data['reason'] ?? 'Rejected by worker';
        $payload['workerRejected'] = true;

        $order->update([
            'workerId' => null,
            'status' => self::STATUS_ACCEPTED,
            'payload' => $payload,
        ]);

        return $order->fresh()->toDocumentArray();
    }

    /**
     * Flutter: Order Assigned → Order Ongoing (schedule-gated).
     */
    public function start(AppUser $user, string $id): array
    {
        $order = $this->requireOrder($user, $id);

        if ($order->status !== self::STATUS_ASSIGNED && $order->status !== self::STATUS_ACCEPTED) {
            throw ValidationException::withMessages([
                'status' => ['Only assigned jobs can be started.'],
            ]);
        }

        $payload = $order->payload ?? [];
        $schedule = $payload['newScheduleDateTime'] ?? $payload['scheduleDateTime'] ?? null;

        if ($schedule && Carbon::parse($schedule)->isFuture()) {
            throw ValidationException::withMessages([
                'schedule' => ['Job cannot be started before the scheduled time.'],
            ]);
        }

        $providerData = $order->provider ?? ($payload['provider'] ?? []);
        $priceUnit = strtolower((string) ($providerData['priceUnit'] ?? ''));

        if ($priceUnit === 'hourly') {
            $payload['startTime'] = now()->toIso8601String();
        }

        $order->update([
            'status' => self::STATUS_ONGOING,
            'payload' => $payload,
        ]);

        return $order->fresh()->toDocumentArray();
    }

    /**
     * Flutter hourly: Stop Time → set endTime + quantity hours.
     */
    public function stopTimer(AppUser $user, string $id): array
    {
        $order = $this->requireOrder($user, $id);

        if ($order->status !== self::STATUS_ONGOING) {
            throw ValidationException::withMessages([
                'status' => ['Timer can only be stopped for ongoing jobs.'],
            ]);
        }

        $payload = $order->payload ?? [];
        $start = isset($payload['startTime']) ? Carbon::parse($payload['startTime']) : null;
        $end = now();
        $payload['endTime'] = $end->toIso8601String();
        $payload['paymentStatus'] = false;

        if ($start) {
            $hours = max(1, (int) ceil($start->diffInMinutes($end) / 60));
            $payload['quantity'] = $hours;
        }

        $order->update(['payload' => $payload]);

        return $order->fresh()->toDocumentArray();
    }

    public function addExtraCharges(AppUser $user, string $id, array $data): array
    {
        $order = $this->requireOrder($user, $id);

        if ($order->status !== self::STATUS_ONGOING) {
            throw ValidationException::withMessages([
                'status' => ['Extra charges can only be added for ongoing jobs.'],
            ]);
        }

        $payload = $order->payload ?? [];

        if (! empty($payload['extraCharges']) && ($payload['extraChargesAdded'] ?? false)) {
            throw ValidationException::withMessages([
                'extraCharges' => ['Extra charges already added.'],
            ]);
        }

        $payload['extraCharges'] = $data['extraCharges'] ?? $data['amount'] ?? 0;
        $payload['extraChargesDescription'] = $data['extraChargesDescription'] ?? $data['description'] ?? null;
        $payload['extraPaymentStatus'] = false;
        $payload['extraChargesAdded'] = true;

        $order->update(['payload' => $payload]);

        return $order->fresh()->toDocumentArray();
    }

    /**
     * Flutter: complete with customer OTP; credit provider wallet if not Fixed.
     */
    public function complete(AppUser $user, string $id, ?string $otp = null): array
    {
        $order = $this->requireOrder($user, $id);

        if ($order->status !== self::STATUS_ONGOING) {
            throw ValidationException::withMessages([
                'status' => ['Only ongoing jobs can be completed.'],
            ]);
        }

        $payload = $order->payload ?? [];
        $orderOtp = (string) ($payload['otp'] ?? '');

        if ($orderOtp !== '' && (string) $otp !== $orderOtp) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid OTP.'],
            ]);
        }

        // Flutter gates: extraPaymentStatus must not be false waiting, or COD/paymentStatus ok
        if (array_key_exists('extraPaymentStatus', $payload) && $payload['extraPaymentStatus'] === false) {
            $paymentMethod = strtolower((string) ($order->payment_method ?? $order->paymentMethod ?? ''));
            $paymentStatus = $payload['paymentStatus'] ?? $order->paymentStatus ?? null;
            if ($paymentMethod !== 'cod' && $paymentStatus === false) {
                throw ValidationException::withMessages([
                    'payment' => ['Waiting for extra charges payment.'],
                ]);
            }
        }

        $payload['endTime'] = $payload['endTime'] ?? now()->toIso8601String();

        $order->update([
            'status' => self::STATUS_COMPLETED,
            'payload' => $payload,
            'paymentStatus' => $order->paymentStatus ?: 'success',
        ]);

        $order = $order->fresh();
        $this->creditProviderWalletOnComplete($order);
        $this->applyReferralIfFirstOrder($order);

        return $order->toDocumentArray();
    }

    public function updateStatus(AppUser $user, string $id, string $status, array $extra = []): array
    {
        return match ($status) {
            self::STATUS_ASSIGNED => $this->accept($user, $id),
            self::STATUS_ONGOING => $this->start($user, $id),
            self::STATUS_COMPLETED => $this->complete($user, $id, $extra['otp'] ?? null),
            self::STATUS_REJECTED => $this->reject($user, $id, $extra),
            default => throw ValidationException::withMessages(['status' => ['Unsupported status transition.']]),
        };
    }

    protected function creditProviderWalletOnComplete(ProviderOrder $order): void
    {
        $providerData = $order->provider ?? ($order->payload['provider'] ?? []);
        $priceUnit = strtolower((string) ($providerData['priceUnit'] ?? 'fixed'));

        // Flutter: only credit when NOT Fixed
        if ($priceUnit === 'fixed') {
            return;
        }

        $providerId = $providerData['author'] ?? data_get($order->payload, 'provider.author');
        if (! $providerId) {
            return;
        }

        $provider = AppUser::query()->where('id', $providerId)->where('role', 'provider')->first();
        if (! $provider) {
            return;
        }

        $price = (float) ($providerData['disPrice'] ?? $providerData['price'] ?? $order->subTotal ?? 0);
        $quantity = (float) ($order->payload['quantity'] ?? 1);
        $extra = (float) ($order->payload['extraCharges'] ?? 0);
        $amount = ($price * max($quantity, 1)) + $extra;

        $commission = (float) ($order->adminCommission ?? data_get($provider->payload, 'adminCommission.commission', 0));
        $commissionType = $order->adminCommissionType ?? data_get($provider->payload, 'adminCommission.type', 'percentage');

        if ($commission > 0) {
            if (strtolower((string) $commissionType) === 'percentage') {
                $amount -= ($amount * $commission / 100);
            } else {
                $amount -= $commission;
            }
        }

        if ($amount <= 0) {
            return;
        }

        $provider->increment('wallet_amount', $amount);

        Wallet::query()->create([
            'id' => (string) Str::uuid(),
            'user_id' => $provider->id,
            'amount' => $amount,
            'isTopUp' => true,
            'payment_method' => $order->payment_method ?? $order->paymentMethod ?? 'booking',
            'payment_status' => 'success',
            'note' => 'Booking completed by worker',
            'order_id' => $order->id,
            'serviceType' => 'ondemand-service',
            'transactionUser' => 'provider',
            'date' => now(),
        ]);
    }

    protected function applyReferralIfFirstOrder(ProviderOrder $order): void
    {
        $authorId = $order->authorID;
        if (! $authorId) {
            return;
        }

        $sectionId = $order->sectionId ?? $order->section_id;
        $previous = ProviderOrder::query()
            ->where('authorID', $authorId)
            ->where('status', self::STATUS_COMPLETED)
            ->where('id', '!=', $order->id)
            ->when($sectionId, function ($q) use ($sectionId) {
                $q->where(function ($q) use ($sectionId) {
                    $q->where('sectionId', $sectionId)->orWhere('section_id', $sectionId);
                });
            })
            ->count();

        if ($previous > 0) {
            return;
        }

        Referral::query()->find($authorId);
        // Referral amount comes from sections; left as hook matching Flutter.
        if ($sectionId) {
            Section::query()->find($sectionId);
        }
    }

    protected function requireOrder(AppUser $user, string $id): ProviderOrder
    {
        $order = $this->findOwnedOrder($user, $id);

        if (! $order) {
            throw ValidationException::withMessages(['id' => ['Job not found.']]);
        }

        return $order;
    }

    protected function findOwnedOrder(AppUser $user, string $id): ?ProviderOrder
    {
        $worker = $this->profileService->getWorkerOrFail($user);

        return ProviderOrder::query()
            ->where('workerId', $worker->id)
            ->where('id', $id)
            ->first();
    }
}
