<?php

namespace App\Services\Driver;

use App\Models\Referral;
use App\Models\Section;
use App\Models\VendorOrder;
use App\Models\Ride;
use App\Models\ParcelOrder;
use App\Models\RentalOrder;
use App\Services\Notifications\FcmNotificationService;
use App\Services\SettingsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DriverReferralService
{
    public function __construct(
        protected SettingsService $settingsService,
        protected DriverWalletService $walletService
    ) {
    }

    public function isFirstOrder(string $authorId, string $orderType): bool
    {
        $models = match ($orderType) {
            'vendor' => [VendorOrder::class],
            'ride' => [Ride::class],
            'parcel' => [ParcelOrder::class],
            'rental' => [RentalOrder::class],
            default => [VendorOrder::class, Ride::class, ParcelOrder::class, RentalOrder::class],
        };

        $completed = ['Order Completed', 'Completed', 'completed'];

        foreach ($models as $modelClass) {
            $count = $modelClass::query()
                ->where('authorID', $authorId)
                ->whereIn('status', $completed)
                ->count();

            if ($count > 1) {
                return false;
            }
        }

        return true;
    }

    public function processReferralReward(Model $order, string $orderType): void
    {
        $authorId = $order->authorID ?? null;

        if (! $authorId || ! $this->isFirstOrder($authorId, $orderType)) {
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

        $amount = $this->resolveReferralAmount($order, $orderType);

        if ($amount <= 0) {
            return;
        }

        $this->walletService->topUp($referralBy, [
            'amount' => $amount,
            'payment_method' => 'Referral Amount',
            'payment_status' => 'success',
            'note' => 'Referral reward for order #' . ($order->id ?? ''),
            'order_id' => $order->id ?? null,
        ]);

        $referral->mergePayload(['isSuccessful' => true]);
        $referral->save();
    }

    protected function resolveReferralAmount(Model $order, string $orderType): float
    {
        $sectionId = $order->sectionId ?? $order->section_id ?? null;

        if ($sectionId) {
            $section = Section::query()->find($sectionId);
            $sectionDoc = $section?->toDocumentArray() ?? [];
            $sectionAmount = (float) ($sectionDoc['referralAmount'] ?? 0);

            if ($sectionAmount > 0) {
                return $sectionAmount;
            }
        }

        if ($orderType === 'vendor') {
            return (float) $this->settingsService->get('referral_amount', ['referralAmount' => 0])['referralAmount'] ?? 0;
        }

        return 0;
    }
}
