<?php

namespace App\Services\Provider;

use App\Models\AppUser;
use App\Models\SubscriptionHistory;
use App\Models\SubscriptionPlan;
use App\Models\Wallet;
use App\Support\CatalogEntityWriter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProviderSubscriptionService
{
    public function plans(?string $sectionId = null, ?bool $isCommissionPlan = null, int $perPage = 50): LengthAwarePaginator
    {
        return SubscriptionPlan::query()
            ->where(function ($q) {
                $q->where('isEnable', true)->orWhere('isActive', true)->orWhereNull('isEnable');
            })
            ->when($sectionId, fn ($q) => $q->where('sectionId', $sectionId))
            ->when($isCommissionPlan !== null, function ($q) use ($isCommissionPlan) {
                if ($isCommissionPlan) {
                    $q->where('payload->isCommissionPlan', true);
                } else {
                    $q->where(function ($q) {
                        $q->where('payload->isCommissionPlan', false)
                            ->orWhereNull('payload->isCommissionPlan');
                    });
                }
            })
            ->orderBy('price')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function history(string $providerId, int $perPage = 20): LengthAwarePaginator
    {
        return SubscriptionHistory::query()
            ->where('user_id', $providerId)
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function subscribe(AppUser $provider, array $data): array
    {
        return $this->activatePlan($provider, $data);
    }

    public function confirmPayment(AppUser $provider, array $data): array
    {
        $plan = $this->findPlan($data['plan_id'] ?? $data['subscriptionPlanId'] ?? null);

        if (! $plan) {
            throw ValidationException::withMessages(['plan_id' => ['Subscription plan not found.']]);
        }

        $planDoc = $plan->toDocumentArray();
        $expectedAmount = (float) ($data['amount'] ?? $planDoc['price'] ?? $plan->price ?? 0);
        $planPrice = (float) ($planDoc['price'] ?? $plan->price ?? 0);

        if ($planPrice > 0 && $expectedAmount < $planPrice) {
            throw ValidationException::withMessages([
                'amount' => ['Payment amount does not match the subscription plan price.'],
            ]);
        }

        if (empty($data['payment_id']) && empty($data['paymentId'])) {
            throw ValidationException::withMessages([
                'payment_id' => ['Payment reference is required.'],
            ]);
        }

        return $this->activatePlan($provider, array_merge($data, [
            'payment_type' => $data['payment_type'] ?? $data['payment_method'] ?? 'gateway',
            'amount' => $expectedAmount,
            'payment_reference' => $data['payment_id'] ?? $data['paymentId'],
        ]));
    }

    protected function activatePlan(AppUser $provider, array $data): array
    {
        $plan = $this->findPlan($data['plan_id'] ?? $data['subscriptionPlanId'] ?? null);

        if (! $plan) {
            throw ValidationException::withMessages(['plan_id' => ['Subscription plan not found.']]);
        }

        $planDoc = $plan->toDocumentArray();
        $expiryDays = (int) ($planDoc['expiryDay'] ?? $plan->payload['expiryDay'] ?? 30);
        $expiryDate = $expiryDays === -1 ? null : Carbon::now()->addDays($expiryDays);

        $history = CatalogEntityWriter::write(new SubscriptionHistory(), [
            'id' => (string) Str::uuid(),
            'user_id' => $provider->id,
            'createdAt' => now(),
            'expiry_date' => $expiryDate?->toIso8601String(),
            'subscription_plan' => $planDoc,
            'payment_type' => $data['payment_type'] ?? $data['payment_method'] ?? 'manual',
            'payment_id' => $data['payment_reference'] ?? $data['payment_id'] ?? $data['paymentId'] ?? null,
        ]);

        $provider->mergePayload([
            'subscriptionPlanId' => $plan->id,
            'subscriptionExpiryDate' => $expiryDate?->toIso8601String(),
            'subscription_plan' => $planDoc,
            'subscriptionTotalOrders' => $planDoc['orderLimit'] ?? $plan->payload['orderLimit'] ?? -1,
        ]);
        $provider->save();

        $chargeAmount = (float) ($data['amount'] ?? $planDoc['price'] ?? 0);

        if ($chargeAmount > 0) {
            Wallet::query()->create([
                'id' => (string) Str::uuid(),
                'user_id' => $provider->id,
                'amount' => $chargeAmount,
                'isTopUp' => false,
                'payment_method' => $data['payment_type'] ?? $data['payment_method'] ?? 'subscription',
                'payment_status' => 'success',
                'note' => 'Subscription purchase: ' . ($planDoc['name'] ?? $plan->title ?? $plan->id),
                'subscription_id' => $plan->id,
                'transactionUser' => 'provider',
                'serviceType' => 'ondemand-service',
                'date' => now(),
            ]);
        }

        return [
            'provider' => $provider->fresh()->toDocumentArray(),
            'history' => $history->toDocumentArray(),
            'plan' => $planDoc,
        ];
    }

    protected function findPlan(?string $planId): ?SubscriptionPlan
    {
        if (! $planId) {
            return null;
        }

        return SubscriptionPlan::query()->find($planId);
    }
}
