<?php

namespace App\Services\Provider;

use App\Models\Setting;
use App\Services\SettingsService;

class ProviderSettingsService
{
    protected array $paymentSettingKeys = [
        'payFastSettings', 'MercadoPago', 'paypalSettings', 'stripeSettings',
        'flutterWave', 'payStack', 'PaytmSettings', 'walletSettings',
        'razorpaySettings', 'midtrans_settings', 'orange_money_settings',
        'xendit_settings', 'arropay_maya_settings',
    ];

    protected array $publicSettingKeys = [
        'provider', 'vendor', 'globalSettings', 'walletSettings', 'Version',
        'placeHolderImage', 'privacyPolicy', 'termsAndConditions',
        'DriverNearBy', 'googleMapKey', 'notification_setting', 'languages',
        'ContactUs', 'emailSetting',
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

        $vendor = $settings['vendor'] ?? [];
        $provider = $settings['provider'] ?? [];

        if (! isset($provider['subscription_model']) && isset($vendor['subscription_model'])) {
            $provider['subscription_model'] = $vendor['subscription_model'];
            $settings['provider'] = $provider;
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
        $raw = $this->settingsService->get('languages', []);
        $list = $raw['list'] ?? (is_array($raw) && array_is_list($raw) ? $raw : []);

        return collect($list)
            ->filter(fn ($item) => (bool) ($item['isActive'] ?? $item['isactive'] ?? true))
            ->map(fn ($item) => [
                'slug' => $item['slug'] ?? $item['code'] ?? null,
                'title' => $item['title'] ?? $item['name'] ?? null,
                'isActive' => (bool) ($item['isActive'] ?? $item['isactive'] ?? true),
            ])
            ->filter(fn ($item) => ! empty($item['slug']))
            ->values()
            ->all();
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
