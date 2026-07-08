<?php

namespace App\Services\Customer;

use App\Models\BannerItem;
use App\Models\Currency;
use App\Models\OnBoarding;
use App\Models\Section;
use App\Models\Setting;
use App\Models\Story;
use App\Models\Zone;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CustomerDashboardService
{
    public function home(?string $sectionId = null): array
    {
        $sections = Section::query()
            ->where('isActive', true)
            ->get()
            ->map(fn ($s) => $s->toDocumentArray());

        $currencies = Currency::query()
            ->where('isActive', true)
            ->get()
            ->map(fn ($c) => $c->toDocumentArray());

        $zones = Zone::query()
            ->where('publish', true)
            ->get()
            ->map(fn ($z) => $z->toDocumentArray());

        $onboarding = OnBoarding::query()
            ->where('type', 'customer')
            ->get()
            ->map(fn ($o) => $o->toDocumentArray());

        $data = [
            'sections' => $sections,
            'currencies' => $currencies,
            'zones' => $zones,
            'onboarding' => $onboarding,
            'settings' => $this->getPublicSettings(),
        ];

        if ($sectionId) {
            $data['banners'] = $this->getBanners($sectionId);
            $data['stories'] = $this->getStories($sectionId);
        }

        return $data;
    }

    public function dashboard(string $customerId, ?string $sectionId = null): array
    {
        return array_merge($this->home($sectionId), [
            'sectionId' => $sectionId,
        ]);
    }

    protected function getBanners(string $sectionId): Collection
    {
        return BannerItem::query()
            ->where('sectionId', $sectionId)
            ->where('is_publish', true)
            ->orderBy('set_order')
            ->get()
            ->map(fn ($b) => $b->toDocumentArray());
    }

    protected function getStories(string $sectionId): Collection
    {
        return Story::query()
            ->where('sectionID', $sectionId)
            ->get()
            ->map(fn ($s) => $s->toDocumentArray());
    }

    protected function getPublicSettings(): array
    {
        $keys = [
            'globalSettings', 'walletSettings', 'Version', 'placeHolderImage',
            'privacyPolicy', 'termsAndConditions', 'maintenance_settings',
            'DriverNearBy', 'DeliveryCharge', 'cashbackOffer', 'story',
        ];

        $settings = [];
        foreach ($keys as $key) {
            $row = Setting::query()->find($key);
            if ($row) {
                $settings[$key] = $row->value ?? $row->toDocumentArray();
            }
        }

        return $settings;
    }
}
