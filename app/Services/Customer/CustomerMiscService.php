<?php

namespace App\Services\Customer;

use App\Models\AppNotification;
use App\Models\Complaint;
use App\Models\DynamicNotification;
use App\Models\EmailTemplate;
use App\Models\GiftCard;
use App\Models\GiftPurchase;
use App\Models\Referral;
use App\Models\Sos;
use App\Models\VendorOrder;
use App\Models\Ride;
use App\Models\ParcelOrder;
use App\Models\RentalOrder;
use App\Models\ProviderOrder;
use App\Services\Customer\CustomerWalletService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerMiscService
{
    public function __construct(protected CustomerWalletService $walletService)
    {
    }

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

    public function validateReferralCode(string $code): ?array
    {
        $referral = Referral::query()
            ->where(function ($query) use ($code) {
                $query->where('payload->referralCode', $code)
                    ->orWhereRaw(
                        "JSON_UNQUOTE(JSON_EXTRACT(payload, '$.referralCode')) = ?",
                        [$code]
                    );
            })
            ->first();

        return $referral?->toDocumentArray();
    }

    public function createReferral(string $customerId, array $data): array
    {
        $payload = [
            'referralCode' => $data['referralCode'],
            'referralBy' => $data['referralBy'] ?? null,
            'isSuccessful' => $data['isSuccessful'] ?? false,
        ];

        $referral = Referral::query()->find($customerId);

        if ($referral) {
            $referral->update([
                'referralBy' => $payload['referralBy'],
                'payload' => array_merge($referral->payload ?? [], $payload),
            ]);
        } else {
            $referral = Referral::query()->create([
                'id' => $customerId,
                'referralBy' => $payload['referralBy'],
                'payload' => $payload,
                'createdAt' => now(),
            ]);
        }

        return $referral->fresh()->toDocumentArray();
    }

    public function notificationContent(string $type): array
    {
        $notification = DynamicNotification::query()
            ->where('type', $type)
            ->first();

        if ($notification) {
            return $notification->toDocumentArray();
        }

        return [
            'id' => '',
            'type' => $type,
            'subject' => 'setup notification',
            'message' => 'Notification setup is pending',
        ];
    }

    public function getComplaintByOrder(string $customerId, string $orderId): ?array
    {
        $complaint = Complaint::query()
            ->where('orderId', $orderId)
            ->where(function ($query) use ($customerId) {
                $query->where('customerId', $customerId)
                    ->orWhere('payload->customerID', $customerId);
            })
            ->orderByDesc('createdAt')
            ->first();

        return $complaint?->toDocumentArray();
    }

    public function complaintExists(string $customerId, string $orderId): bool
    {
        return Complaint::query()
            ->where('orderId', $orderId)
            ->where(function ($query) use ($customerId) {
                $query->where('customerId', $customerId)
                    ->orWhere('payload->customerID', $customerId);
            })
            ->exists();
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
        $driverId = $data['driverId'] ?? $data['driverID'] ?? null;

        $payload = array_filter([
            'description' => $data['description'] ?? null,
            'driverName' => $data['driverName'] ?? null,
            'customerName' => $data['customerName'] ?? null,
            'status' => $data['status'] ?? 'Initiated',
        ], static fn ($value) => $value !== null);

        $complaint = Complaint::query()->create([
            'id' => $data['id'] ?? (string) Str::uuid(),
            'orderId' => $data['orderId'],
            'title' => $data['title'],
            'driverId' => $driverId,
            'customerId' => $customerId,
            'createdAt' => $data['createdAt'] ?? now(),
            'payload' => $payload,
        ]);

        return $complaint->toDocumentArray();
    }

    public function createSos(string $customerId, array $data): array
    {
        $payload = array_filter([
            'customerId' => $customerId,
            'status' => $data['status'] ?? 'Initiated',
            'latLong' => $data['latLong'] ?? null,
        ], static fn ($value) => $value !== null);

        $sos = Sos::query()->create([
            'id' => $data['id'] ?? (string) Str::uuid(),
            'orderId' => $data['orderId'],
            'createdAt' => $data['createdAt'] ?? now(),
            'payload' => $payload,
        ]);

        return $sos->toDocumentArray();
    }

    public function giftCardHistory(string $customerId, int $perPage = 20): LengthAwarePaginator
    {
        return GiftPurchase::query()
            ->where('userid', $customerId)
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function redeemGiftCard(string $customerId, string $giftCode): array
    {
        $purchase = GiftPurchase::query()
            ->where(function ($query) use ($giftCode) {
                $query->where('payload->giftCode', $giftCode)
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.giftCode')) = ?", [$giftCode]);
            })
            ->first();

        if (! $purchase) {
            throw ValidationException::withMessages([
                'giftCode' => ['Invalid gift card code.'],
            ]);
        }

        $doc = $purchase->toDocumentArray();

        if (! empty($doc['isRedeem']) || ! empty($doc['redeem'])) {
            throw ValidationException::withMessages([
                'giftCode' => ['This gift card has already been redeemed.'],
            ]);
        }

        $amount = (float) ($doc['price'] ?? $doc['amount'] ?? 0);

        $this->walletService->topUp($customerId, [
            'amount' => $amount,
            'payment_method' => 'gift_card',
            'payment_status' => 'success',
            'note' => 'Gift card redemption: ' . $giftCode,
        ]);

        $purchase->update([
            'payload' => array_merge($purchase->payload ?? [], [
                'isRedeem' => true,
                'redeemBy' => $customerId,
                'redeemAt' => now()->toIso8601String(),
            ]),
        ]);

        return $purchase->fresh()->toDocumentArray();
    }

    public function processReferralRewards(string $customerId): array
    {
        $rewards = [];
        $pendingReferrals = Referral::query()
            ->where('referralBy', $customerId)
            ->get()
            ->filter(function ($referral) {
                $doc = $referral->toDocumentArray();

                return empty($doc['isSuccessful']);
            });

        foreach ($pendingReferrals as $referral) {
            $friendId = $referral->id;
            $hasCompletedOrder = $this->friendHasCompletedOrder($friendId);

            if (! $hasCompletedOrder) {
                continue;
            }

            $section = $referral->toDocumentArray();
            $amount = (float) ($section['referralAmount'] ?? 0);

            if ($amount <= 0) {
                continue;
            }

            $this->walletService->topUp($customerId, [
                'amount' => $amount,
                'payment_method' => 'Referral',
                'payment_status' => 'success',
                'note' => 'Referral reward',
            ]);

            $referral->mergePayload(['isSuccessful' => true]);
            $referral->save();
            $rewards[] = $referral->fresh()->toDocumentArray();
        }

        return $rewards;
    }

    public function emailTemplate(string $type): ?array
    {
        $template = EmailTemplate::query()
            ->where('type', $type)
            ->first();

        return $template?->toDocumentArray();
    }

    public function markNotificationRead(string $notificationId): ?array
    {
        $notification = AppNotification::query()->find($notificationId);

        if (! $notification) {
            return null;
        }

        $notification->mergePayload(['isRead' => true]);
        $notification->save();

        return $notification->fresh()->toDocumentArray();
    }

    protected function friendHasCompletedOrder(string $friendId): bool
    {
        $models = [VendorOrder::class, Ride::class, ParcelOrder::class, RentalOrder::class, ProviderOrder::class];

        foreach ($models as $modelClass) {
            $exists = $modelClass::query()
                ->where('authorID', $friendId)
                ->whereIn('status', ['Order Completed', 'Completed', 'completed'])
                ->exists();

            if ($exists) {
                return true;
            }
        }

        return false;
    }
}
