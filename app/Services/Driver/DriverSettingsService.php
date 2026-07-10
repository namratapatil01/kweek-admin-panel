<?php

namespace App\Services\Driver;

use App\Models\Setting;
use App\Models\Tax;
use App\Services\SettingsService;

class DriverSettingsService
{
    protected array $paymentSettingKeys = [
        'payFastSettings', 'MercadoPago', 'paypalSettings', 'stripeSettings',
        'flutterWave', 'payStack', 'PaytmSettings', 'walletSettings',
        'razorpaySettings', 'CODSettings', 'midtrans_settings', 'orange_money_settings',
        'xendit_settings', 'arropay_maya_settings', 'maya_qr_settings', 'arropay_settings',
    ];

    protected array $publicSettingKeys = [
        'globalSettings', 'walletSettings', 'Version', 'placeHolderImage',
        'privacyPolicy', 'termsAndConditions', 'maintenance_settings',
        'DriverNearBy', 'document_verification_settings', 'googleMapKey',
        'notification_setting', 'languages', 'RestaurantNearBy',
    ];

    public function __construct(protected SettingsService $settingsService)
    {
    }

    public function index(): array
    {
        $settings = [];

        foreach ($this->publicSettingKeys as $key) {
            $row = Setting::query()->find($key);
            if ($row) {
                $settings[$key] = $row->value ?? $row->toDocumentArray();
            }
        }

        return $settings;
    }

    public function paymentSettings(): array
    {
        $settings = [];

        foreach ($this->paymentSettingKeys as $key) {
            $row = Setting::query()->find($key);
            if ($row) {
                $settings[$key] = $row->value ?? $row->toDocumentArray();
            }
        }

        return $settings;
    }

    public function languages(): array
    {
        return $this->settingsService->get('languages', []);
    }

    public function taxes(?string $country = null): array
    {
        $query = Tax::query()->published();

        if ($country) {
            $query->where(function ($q) use ($country) {
                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.country')) = ?", [$country])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.country')) IS NULL");
            });
        }

        return $query->orderBy('title')->get()->map(fn ($item) => $item->toDocumentArray())->values()->all();
    }

    public function setting(string $key): ?array
    {
        $row = Setting::query()->find($key);

        if (! $row) {
            return null;
        }

        return $row->value ?? $row->toDocumentArray();
    }
}
