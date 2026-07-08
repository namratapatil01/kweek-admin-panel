<?php

namespace App\Services\Provider;

use App\Models\AppUser;
use App\Models\Currency;
use App\Models\OnBoarding;
use App\Models\ProviderOrder;
use App\Models\ProviderService;
use App\Models\Section;
use App\Services\SettingsService;
use Illuminate\Support\Carbon;

class ProviderDashboardService
{
    public function __construct(protected SettingsService $settingsService)
    {
    }

    public function dashboard(AppUser $provider): array
    {
        $providerId = $provider->id;

        $newCount = $this->ordersForProvider($providerId)
            ->where('status', 'Order Placed')
            ->count();

        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        $todayCount = $this->ordersForProvider($providerId)
            ->whereIn('status', ['Order Accepted', 'Order Assigned', 'Order Ongoing'])
            ->where(function ($q) use ($todayStart, $todayEnd) {
                $q->whereBetween('payload->newScheduleDateTime', [$todayStart->toIso8601String(), $todayEnd->toIso8601String()])
                    ->orWhereBetween('createdAt', [$todayStart, $todayEnd]);
            })
            ->count();

        $completedCount = $this->ordersForProvider($providerId)
            ->where('status', 'Order Completed')
            ->count();

        $cancelledCount = $this->ordersForProvider($providerId)
            ->whereIn('status', ['Order Rejected', 'Order Cancelled'])
            ->count();

        $servicesCount = ProviderService::query()
            ->where('payload->author', $providerId)
            ->count();

        $sectionId = $provider->sectionId ?? $provider->section_id;
        $section = $sectionId ? Section::query()->find($sectionId) : null;

        return [
            'provider' => $provider->toDocumentArray(),
            'counts' => [
                'new_bookings' => $newCount,
                'today_bookings' => $todayCount,
                'completed_bookings' => $completedCount,
                'cancelled_bookings' => $cancelledCount,
                'services' => $servicesCount,
            ],
            'wallet_amount' => (float) ($provider->wallet_amount ?? 0),
            'section' => $section?->toDocumentArray(),
            'subscription' => [
                'subscriptionPlanId' => data_get($provider->payload, 'subscriptionPlanId'),
                'subscriptionExpiryDate' => data_get($provider->payload, 'subscriptionExpiryDate'),
                'subscription_plan' => data_get($provider->payload, 'subscription_plan'),
                'subscriptionTotalOrders' => data_get($provider->payload, 'subscriptionTotalOrders'),
            ],
            'settings' => [
                'provider' => $this->settingsService->get('provider', []),
                'globalSettings' => $this->settingsService->get('globalSettings', []),
                'Version' => $this->settingsService->get('Version', []),
            ],
        ];
    }

    public function home(): array
    {
        return [
            'on_boarding' => OnBoarding::query()
                ->where('type', 'provider')
                ->get()
                ->map(fn ($item) => $item->toDocumentArray())
                ->values()
                ->all(),
            'sections' => Section::query()
                ->where('isActive', true)
                ->where(function ($q) {
                    $q->where('serviceTypeFlag', 'ondemand-service')
                        ->orWhere('serviceType', 'ondemand-service');
                })
                ->get()
                ->map(fn ($item) => $item->toDocumentArray())
                ->values()
                ->all(),
            'currency' => Currency::query()
                ->where('isActive', true)
                ->first()?->toDocumentArray(),
            'settings' => [
                'provider' => $this->settingsService->get('provider', []),
                'termsAndConditions' => $this->settingsService->get('termsAndConditions', []),
                'privacyPolicy' => $this->settingsService->get('privacyPolicy', []),
                'globalSettings' => $this->settingsService->get('globalSettings', []),
            ],
        ];
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
