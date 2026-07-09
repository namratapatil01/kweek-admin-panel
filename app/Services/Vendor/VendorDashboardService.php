<?php

namespace App\Services\Vendor;

use App\Models\AppUser;
use App\Models\Currency;
use App\Models\OnBoarding;
use App\Models\Section;
use App\Models\VendorOrder;
use App\Services\SettingsService;

class VendorDashboardService
{
    public function __construct(
        protected SettingsService $settingsService,
        protected VendorProfileService $profileService
    ) {
    }

    public function home(?string $sectionId = null): array
    {
        $sectionsQuery = Section::query()->where('isActive', true);
        if ($sectionId) {
            $sectionsQuery->where('id', $sectionId);
        }

        return [
            'on_boarding' => OnBoarding::query()
                ->where('type', 'store')
                ->get()
                ->map(fn ($item) => $item->toDocumentArray())
                ->values()
                ->all(),
            'sections' => $sectionsQuery->get()->map(fn ($item) => $item->toDocumentArray())->values()->all(),
            'currency' => Currency::query()->where('isActive', true)->first()?->toDocumentArray(),
            'settings' => [
                'globalSettings' => $this->settingsService->get('globalSettings', []),
                'vendor' => $this->settingsService->get('vendor', []),
                'DriverNearBy' => $this->settingsService->get('DriverNearBy', []),
                'DeliveryCharge' => $this->settingsService->get('DeliveryCharge', []),
                'document_verification_settings' => $this->settingsService->get('document_verification_settings', []),
                'maintenance_settings' => $this->settingsService->get('maintenance_settings', []),
                'specialDiscountOffer' => $this->settingsService->get('specialDiscountOffer', []),
                'termsAndConditions' => $this->settingsService->get('termsAndConditions', []),
                'privacyPolicy' => $this->settingsService->get('privacyPolicy', []),
                'Version' => $this->settingsService->get('Version', []),
            ],
        ];
    }

    public function dashboard(AppUser $user): array
    {
        $store = $this->profileService->getStore($user);
        $vendorId = $user->vendorID;

        $counts = ['new' => 0, 'active' => 0, 'completed' => 0, 'cancelled' => 0];
        if ($vendorId) {
            $base = VendorOrder::query()->where('vendorID', $vendorId);
            $counts = [
                'new' => (clone $base)->where('status', VendorOrderService::STATUS_PLACED)->count(),
                'active' => (clone $base)->whereIn('status', [
                    VendorOrderService::STATUS_ACCEPTED,
                    VendorOrderService::STATUS_DRIVER_PENDING,
                    VendorOrderService::STATUS_DRIVER_ACCEPTED,
                    VendorOrderService::STATUS_SHIPPED,
                    VendorOrderService::STATUS_IN_TRANSIT,
                ])->count(),
                'completed' => (clone $base)->where('status', VendorOrderService::STATUS_COMPLETED)->count(),
                'cancelled' => (clone $base)->whereIn('status', [
                    VendorOrderService::STATUS_CANCELLED,
                    VendorOrderService::STATUS_REJECTED,
                    VendorOrderService::STATUS_DRIVER_REJECTED,
                ])->count(),
            ];
        }

        return [
            'vendor' => $user->toDocumentArray(),
            'store' => $store?->toDocumentArray(),
            'counts' => $counts,
            'wallet_amount' => (float) ($user->wallet_amount ?? 0),
            'has_store' => (bool) $store,
            'isDocumentVerify' => (bool) $user->isDocumentVerify,
            'settings' => [
                'vendor' => $this->settingsService->get('vendor', []),
                'globalSettings' => $this->settingsService->get('globalSettings', []),
            ],
        ];
    }
}
