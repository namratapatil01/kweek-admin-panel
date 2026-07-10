<?php

namespace App\Services\Provider;

use App\Models\AppUser;
use App\Models\ProviderOrder;
use App\Models\ProviderWorker;
use App\Models\Referral;
use App\Models\Section;
use App\Models\Wallet;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProviderBookingService
{
    public const STATUS_PLACED = 'Order Placed';
    public const STATUS_ACCEPTED = 'Order Accepted';
    public const STATUS_ASSIGNED = 'Order Assigned';
    public const STATUS_ONGOING = 'Order Ongoing';
    public const STATUS_COMPLETED = 'Order Completed';
    public const STATUS_REJECTED = 'Order Rejected';
    public const STATUS_CANCELLED = 'Order Cancelled';

    public function __construct(
        protected ProviderNotificationService $notificationService
    ) {
    }

    public function list(string $providerId, ?string $tab = null, int $perPage = 20, ?string $since = null): LengthAwarePaginator
    {
        $query = $this->ordersForProvider($providerId);

        if ($since) {
            try {
                $sinceAt = Carbon::parse($since);
                $query->where(function ($q) use ($sinceAt) {
                    $q->where('updated_at', '>=', $sinceAt)
                        ->orWhere('createdAt', '>=', $sinceAt);
                });
            } catch (\Throwable) {
                // ignore invalid since
            }
        }

        $tab = $tab ?: 'new';

        match ($tab) {
            'new' => $query->where('status', self::STATUS_PLACED),
            'today' => $query->whereIn('status', [self::STATUS_ACCEPTED, self::STATUS_ASSIGNED, self::STATUS_ONGOING])
                ->where(function ($q) {
                    $start = Carbon::today()->startOfDay();
                    $end = Carbon::today()->endOfDay();
                    $q->whereBetween('payload->newScheduleDateTime', [$start->toIso8601String(), $end->toIso8601String()])
                        ->orWhereBetween('createdAt', [$start, $end]);
                }),
            'upcoming' => $query->whereIn('status', [self::STATUS_ACCEPTED, self::STATUS_ASSIGNED])
                ->where('payload->newScheduleDateTime', '>', Carbon::tomorrow()->startOfDay()->toIso8601String()),
            'completed' => $query->where('status', self::STATUS_COMPLETED),
            'cancelled' => $query->whereIn('status', [self::STATUS_REJECTED, self::STATUS_CANCELLED]),
            default => null,
        };

        return $query->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function show(string $providerId, string $id): ?array
    {
        $order = $this->findOwnedOrder($providerId, $id);

        return $order?->toDocumentArray();
    }

    public function accept(AppUser $provider, string $id, array $data = []): array
    {
        $order = $this->findOwnedOrder($provider->id, $id);
        if (! $order) {
            throw ValidationException::withMessages(['id' => ['Booking not found.']]);
        }

        if ($order->status !== self::STATUS_PLACED) {
            throw ValidationException::withMessages(['status' => ['Only placed bookings can be accepted.']]);
        }

        $payload = $order->payload ?? [];
        if (! empty($data['newScheduleDateTime'])) {
            $payload['newScheduleDateTime'] = $data['newScheduleDateTime'];
        } elseif (! empty($data['scheduleDateTime'])) {
            $payload['newScheduleDateTime'] = $data['scheduleDateTime'];
        }

        $order->update([
            'status' => self::STATUS_ACCEPTED,
            'payload' => $payload,
        ]);

        $this->creditWalletOnAccept($provider, $order);
        $this->notifyCustomer($order->fresh(), 'provider_accepted');

        return $order->fresh()->toDocumentArray();
    }

    public function reject(AppUser $provider, string $id, array $data = []): array
    {
        $order = $this->findOwnedOrder($provider->id, $id);
        if (! $order) {
            throw ValidationException::withMessages(['id' => ['Booking not found.']]);
        }

        $payload = $order->payload ?? [];
        $payload['reason'] = $data['reason'] ?? 'Rejected by provider';

        $order->update([
            'status' => self::STATUS_REJECTED,
            'payload' => $payload,
        ]);

        $order = $order->fresh();
        $this->refundCustomerOnReject($order);
        $this->notifyCustomer($order, 'provider_rejected');

        return $order->toDocumentArray();
    }

    public function assignWorker(AppUser $provider, string $id, string $workerId): array
    {
        $order = $this->findOwnedOrder($provider->id, $id);
        if (! $order) {
            throw ValidationException::withMessages(['id' => ['Booking not found.']]);
        }

        $worker = ProviderWorker::query()
            ->where('id', $workerId)
            ->where('providerId', $provider->id)
            ->first();

        if (! $worker) {
            throw ValidationException::withMessages(['workerId' => ['Worker not found.']]);
        }

        $order->update([
            'workerId' => $workerId,
            'status' => self::STATUS_ASSIGNED,
        ]);

        $order = $order->fresh();
        $this->notifyCustomer($order, 'worker_assigned');

        $workerUser = AppUser::query()->find($workerId);
        if ($workerUser?->fcmToken) {
            $this->notificationService->sendBookingNotification(
                $workerUser->fcmToken,
                'worker_assigned',
                $order->id
            );
        }

        return $order->toDocumentArray();
    }

    public function start(AppUser $provider, string $id): array
    {
        $order = $this->findOwnedOrder($provider->id, $id);
        if (! $order) {
            throw ValidationException::withMessages(['id' => ['Booking not found.']]);
        }

        $payload = $order->payload ?? [];
        $payload['startTime'] = now()->toIso8601String();

        $order->update([
            'status' => self::STATUS_ONGOING,
            'payload' => $payload,
        ]);

        $order = $order->fresh();
        $this->notifyCustomer($order, 'service_intransit');

        return $order->toDocumentArray();
    }

    public function stopTimer(AppUser $provider, string $id): array
    {
        $order = $this->findOwnedOrder($provider->id, $id);
        if (! $order) {
            throw ValidationException::withMessages(['id' => ['Booking not found.']]);
        }

        $payload = $order->payload ?? [];
        $payload['endTime'] = now()->toIso8601String();
        $order->update(['payload' => $payload]);

        $order = $order->fresh();
        $this->notifyCustomer($order, 'stop_time');

        return $order->toDocumentArray();
    }

    public function addExtraCharges(AppUser $provider, string $id, array $data): array
    {
        $order = $this->findOwnedOrder($provider->id, $id);
        if (! $order) {
            throw ValidationException::withMessages(['id' => ['Booking not found.']]);
        }

        $payload = $order->payload ?? [];
        $payload['extraCharges'] = $data['extraCharges'] ?? $data['amount'] ?? 0;
        $payload['extraChargesDescription'] = $data['extraChargesDescription'] ?? $data['description'] ?? null;
        $payload['extraPaymentStatus'] = false;

        $order->update(['payload' => $payload]);

        $order = $order->fresh();
        $this->notifyCustomer($order, 'service_charges');

        return $order->toDocumentArray();
    }

    public function complete(AppUser $provider, string $id, ?string $otp = null): array
    {
        $order = $this->findOwnedOrder($provider->id, $id);
        if (! $order) {
            throw ValidationException::withMessages(['id' => ['Booking not found.']]);
        }

        $orderOtp = (string) ($order->payload['otp'] ?? '');
        if ($orderOtp !== '' && (string) $otp !== $orderOtp) {
            throw ValidationException::withMessages(['otp' => ['Invalid OTP.']]);
        }

        $payload = $order->payload ?? [];
        $payload['endTime'] = $payload['endTime'] ?? now()->toIso8601String();

        $order->update([
            'status' => self::STATUS_COMPLETED,
            'payload' => $payload,
            'paymentStatus' => $order->paymentStatus ?: 'success',
        ]);

        $order = $order->fresh();
        $this->creditWalletOnComplete($provider, $order);
        $this->applyReferralIfFirstOrder($order);
        $this->notifyCustomer($order, 'service_completed');

        return $order->toDocumentArray();
    }

    public function updateStatus(AppUser $provider, string $id, string $status, array $extra = []): array
    {
        return match ($status) {
            self::STATUS_ACCEPTED => $this->accept($provider, $id, $extra),
            self::STATUS_REJECTED => $this->reject($provider, $id, $extra),
            self::STATUS_ONGOING => $this->start($provider, $id),
            self::STATUS_COMPLETED => $this->complete($provider, $id, $extra['otp'] ?? null),
            self::STATUS_ASSIGNED => $this->assignWorker($provider, $id, $extra['workerId'] ?? ''),
            default => throw ValidationException::withMessages(['status' => ['Unsupported status transition.']]),
        };
    }

    protected function creditWalletOnAccept(AppUser $provider, ProviderOrder $order): void
    {
        $providerData = $order->provider ?? ($order->payload['provider'] ?? []);
        $priceUnit = $providerData['priceUnit'] ?? data_get($providerData, 'priceUnit');

        if ($priceUnit && strtolower((string) $priceUnit) !== 'fixed') {
            return;
        }

        $this->creditOrderAmount($provider, $order, 'Booking accepted');
        $this->decrementSubscriptionOrders($provider);
    }

    protected function creditWalletOnComplete(AppUser $provider, ProviderOrder $order): void
    {
        $providerData = $order->provider ?? ($order->payload['provider'] ?? []);
        $priceUnit = $providerData['priceUnit'] ?? data_get($providerData, 'priceUnit');

        if (! $priceUnit || strtolower((string) $priceUnit) === 'fixed') {
            return;
        }

        $this->creditOrderAmount($provider, $order, 'Booking completed');
        $this->decrementSubscriptionOrders($provider);
    }

    protected function creditOrderAmount(AppUser $provider, ProviderOrder $order, string $note): void
    {
        $providerData = $order->provider ?? ($order->payload['provider'] ?? []);
        $price = (float) ($providerData['disPrice'] ?? $providerData['price'] ?? $order->subTotal ?? 0);
        $quantity = (float) ($order->payload['quantity'] ?? 1);
        $amount = $price * max($quantity, 1);

        $commission = (float) ($order->adminCommission ?? data_get($provider->payload, 'adminCommission.commission', 0));
        $commissionType = $order->adminCommissionType ?? data_get($provider->payload, 'adminCommission.type', 'percentage');

        if ($commission > 0) {
            if ($commissionType === 'percentage' || $commissionType === 'Percentage') {
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
            'note' => $note,
            'order_id' => $order->id,
            'serviceType' => 'ondemand-service',
            'transactionUser' => 'provider',
            'date' => now(),
        ]);
    }

    protected function refundCustomerOnReject(ProviderOrder $order): void
    {
        $providerData = $order->provider ?? ($order->payload['provider'] ?? []);
        $priceUnit = $providerData['priceUnit'] ?? data_get($providerData, 'priceUnit');

        if (strtolower((string) $priceUnit) !== 'fixed') {
            return;
        }

        $paymentMethod = strtolower((string) ($order->payment_method ?? $order->paymentMethod ?? ''));
        if ($paymentMethod === 'cod') {
            return;
        }

        $authorId = $order->authorID ?? data_get($order->payload, 'author.userID');
        if (! $authorId) {
            return;
        }

        $customer = AppUser::query()->where('id', $authorId)->where('role', 'customer')->first();
        if (! $customer) {
            return;
        }

        $price = (float) ($providerData['disPrice'] ?? $providerData['price'] ?? $order->subTotal ?? 0);
        $quantity = (float) ($order->payload['quantity'] ?? 1);
        $discount = (float) ($order->discount ?? data_get($order->payload, 'discount', 0));
        $amount = max(($price * max($quantity, 1)) - $discount, 0);

        if ($amount <= 0) {
            return;
        }

        $customer->increment('wallet_amount', $amount);

        Wallet::query()->create([
            'id' => (string) Str::uuid(),
            'user_id' => $customer->id,
            'amount' => $amount,
            'isTopUp' => true,
            'payment_method' => 'Wallet',
            'payment_status' => 'success',
            'note' => 'Booking amount Refund',
            'order_id' => $order->id,
            'serviceType' => 'ondemand-service',
            'transactionUser' => 'customer',
            'date' => now(),
        ]);
    }

    protected function decrementSubscriptionOrders(AppUser $provider): void
    {
        $total = data_get($provider->payload, 'subscriptionTotalOrders');
        if ($total === null || $total === '' || (int) $total < 0) {
            return;
        }

        $provider->mergePayload([
            'subscriptionTotalOrders' => max(0, (int) $total - 1),
        ]);
        $provider->save();
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

        $referral = Referral::query()->find($authorId);
        if (! $referral) {
            return;
        }

        $doc = $referral->toDocumentArray();
        $referralBy = $doc['referralBy'] ?? $referral->referralBy ?? null;

        if (! $referralBy) {
            return;
        }

        $amount = 0.0;
        if ($sectionId) {
            $section = Section::query()->find($sectionId);
            $amount = (float) ($section?->toDocumentArray()['referralAmount'] ?? 0);
        }

        if ($amount <= 0) {
            return;
        }

        $referrer = AppUser::query()->find($referralBy);
        if (! $referrer) {
            return;
        }

        $referrer->increment('wallet_amount', $amount);

        Wallet::query()->create([
            'id' => (string) Str::uuid(),
            'user_id' => $referrer->id,
            'amount' => $amount,
            'isTopUp' => true,
            'payment_method' => 'Referral Amount',
            'payment_status' => 'success',
            'note' => 'Referral reward for booking #' . $order->id,
            'order_id' => $order->id,
            'serviceType' => 'ondemand-service',
            'transactionUser' => 'driver',
            'date' => now(),
        ]);

        $referral->mergePayload(['isSuccessful' => true]);
        $referral->save();
    }

    protected function notifyCustomer(ProviderOrder $order, string $type): void
    {
        $orderDoc = $order->toDocumentArray();
        $fcmToken = data_get($orderDoc, 'author.fcmToken')
            ?? data_get($order->payload, 'author.fcmToken');

        if (! $fcmToken) {
            $authorId = $order->authorID ?? data_get($order->payload, 'author.userID');
            if ($authorId) {
                $fcmToken = AppUser::query()->find($authorId)?->fcmToken;
            }
        }

        $this->notificationService->sendBookingNotification($fcmToken, $type, $order->id);
    }

    protected function findOwnedOrder(string $providerId, string $id): ?ProviderOrder
    {
        return $this->ordersForProvider($providerId)->where('id', $id)->first();
    }

    protected function ordersForProvider(string $providerId)
    {
        return ProviderOrder::query()->where(function ($q) use ($providerId) {
            $q->where('provider->author', $providerId)
                ->orWhere('payload->provider.author', $providerId)
                ->orWhere('payload->providerId', $providerId);
        });
    }
}
