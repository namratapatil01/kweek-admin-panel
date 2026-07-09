<?php

namespace App\Services\Driver;

use App\Models\AppUser;
use App\Models\Currency;
use App\Models\OnBoarding;
use App\Models\ParcelOrder;
use App\Models\RentalOrder;
use App\Models\Ride;
use App\Models\Section;
use App\Models\VendorOrder;
use App\Services\SettingsService;

class DriverDashboardService
{
    public function __construct(protected SettingsService $settingsService)
    {
    }

    public function home(?string $serviceType = null): array
    {
        $sectionsQuery = Section::query()->where('isActive', true);
        if ($serviceType) {
            $sectionsQuery->where(function ($q) use ($serviceType) {
                $q->where('serviceTypeFlag', $serviceType)
                    ->orWhere('serviceType', $serviceType);
            });
        }

        return [
            'on_boarding' => OnBoarding::query()
                ->where('type', 'driver')
                ->get()
                ->map(fn ($item) => $item->toDocumentArray())
                ->values()
                ->all(),
            'sections' => $sectionsQuery->get()->map(fn ($item) => $item->toDocumentArray())->values()->all(),
            'currency' => Currency::query()->where('isActive', true)->first()?->toDocumentArray(),
            'settings' => [
                'globalSettings' => $this->settingsService->get('globalSettings', []),
                'DriverNearBy' => $this->settingsService->get('DriverNearBy', []),
                'document_verification_settings' => $this->settingsService->get('document_verification_settings', []),
                'maintenance_settings' => $this->settingsService->get('maintenance_settings', []),
                'termsAndConditions' => $this->settingsService->get('termsAndConditions', []),
                'privacyPolicy' => $this->settingsService->get('privacyPolicy', []),
                'Version' => $this->settingsService->get('Version', []),
            ],
        ];
    }

    public function dashboard(AppUser $driver): array
    {
        $type = $this->resolveOrderType($driver);
        $driverId = $driver->id;

        $pending = $this->countByTab($driver, $type, 'pending');
        $active = $this->countByTab($driver, $type, 'active');
        $completed = $this->countByTab($driver, $type, 'completed');
        $available = $this->countByTab($driver, $type, 'available');

        return [
            'driver' => $driver->toDocumentArray(),
            'serviceType' => $driver->serviceType,
            'counts' => [
                'pending' => $pending,
                'active' => $active,
                'completed' => $completed,
                'available' => $available,
            ],
            'wallet_amount' => (float) ($driver->wallet_amount ?? 0),
            'online' => (bool) $driver->isActive,
            'isDocumentVerify' => (bool) $driver->isDocumentVerify,
            'settings' => [
                'DriverNearBy' => $this->settingsService->get('DriverNearBy', []),
                'globalSettings' => $this->settingsService->get('globalSettings', []),
            ],
        ];
    }

    protected function resolveOrderType(AppUser $driver): string
    {
        return match ($driver->serviceType) {
            'cab-service' => 'ride',
            'parcel_delivery' => 'parcel',
            'rental-service' => 'rental',
            default => 'vendor',
        };
    }

    protected function countByTab(AppUser $driver, string $type, string $tab): int
    {
        $statuses = app(DriverOrderService::class)->statusesForTab($tab, $type);

        if ($tab === 'available') {
            return app(DriverOrderService::class)->availableQuery($driver, $type, $statuses)->count();
        }

        return app(DriverOrderService::class)->queryForDriver($driver, $type)
            ->whereIn('status', $statuses)
            ->count();
    }
}
