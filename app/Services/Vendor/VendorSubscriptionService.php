<?php

namespace App\Services\Vendor;

use App\Models\AppUser;
use App\Models\SubscriptionHistory;
use App\Models\SubscriptionPlan;
use App\Models\Vendor;
use App\Models\Wallet;
use App\Support\CatalogEntityWriter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VendorSubscriptionService
{
    public function plans(?string $sectionId = null, int $perPage = 50): LengthAwarePaginator
    {
        return SubscriptionPlan::query()
            ->where(function ($q) {
                $q->where('isEnable', true)->orWhere('isActive', true)->orWhereNull('isEnable');
            })
            ->when($sectionId, fn ($q) => $q->where('sectionId', $sectionId))
            ->orderBy('price')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function history(string $userId, int $perPage = 20): LengthAwarePaginator
    {
        return SubscriptionHistory::query()
            ->where('user_id', $userId)
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function subscribe(AppUser $user, array $data): array
    {
        $plan = SubscriptionPlan::query()->find($data['plan_id'] ?? $data['subscriptionPlanId'] ?? null);
        if (! $plan) {
            throw ValidationException::withMessages(['plan_id' => ['Subscription plan not found.']]);
        }

        $planDoc = $plan->toDocumentArray();
        $expiryDays = (int) ($planDoc['expiryDay'] ?? $plan->payload['expiryDay'] ?? 30);
        $expiryDate = Carbon::now()->addDays($expiryDays);

        $history = CatalogEntityWriter::write(new SubscriptionHistory(), [
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'createdAt' => now(),
            'expiry_date' => $expiryDate->toIso8601String(),
            'subscription_plan' => $planDoc,
            'payment_type' => $data['payment_type'] ?? $data['payment_method'] ?? 'manual',
        ]);

        $user->mergePayload([
            'subscriptionPlanId' => $plan->id,
            'subscriptionExpiryDate' => $expiryDate->toIso8601String(),
            'subscription_plan' => $planDoc,
            'subscriptionTotalOrders' => $planDoc['orderLimit'] ?? $plan->payload['orderLimit'] ?? -1,
        ]);
        $user->save();

        if ($user->vendorID) {
            $store = Vendor::query()->find($user->vendorID);
            if ($store) {
                $store->mergePayload([
                    'subscriptionPlanId' => $plan->id,
                    'subscriptionExpiryDate' => $expiryDate->toIso8601String(),
                    'subscription_plan' => $planDoc,
                    'subscriptionTotalOrders' => $planDoc['orderLimit'] ?? -1,
                ]);
                $store->save();
            }
        }

        if (! empty($data['amount']) || ! empty($planDoc['price'])) {
            Wallet::query()->create([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'amount' => (float) ($data['amount'] ?? $planDoc['price'] ?? 0),
                'isTopUp' => false,
                'payment_method' => $data['payment_type'] ?? 'subscription',
                'payment_status' => 'success',
                'note' => 'Subscription purchase: ' . ($planDoc['name'] ?? $plan->id),
                'subscription_id' => $plan->id,
                'transactionUser' => 'vendor',
                'date' => now(),
            ]);
        }

        return [
            'vendor' => $user->fresh()->toDocumentArray(),
            'history' => $history->toDocumentArray(),
            'plan' => $planDoc,
        ];
    }
}
