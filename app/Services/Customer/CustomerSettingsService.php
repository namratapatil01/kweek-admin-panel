<?php

namespace App\Services\Customer;

use App\Models\Setting;
use App\Services\SettingsService;

class CustomerSettingsService
{
    protected array $paymentSettingKeys = [
        'payFastSettings', 'MercadoPago', 'paypalSettings', 'stripeSettings',
        'flutterWave', 'payStack', 'PaytmSettings', 'walletSettings',
        'razorpaySettings', 'CODSettings', 'midtrans_settings', 'orange_money_settings',
        'xendit_settings', 'maya_qr_settings', 'arropay_settings',
    ];

    protected array $publicSettingKeys = [
        'globalSettings', 'walletSettings', 'Version', 'placeHolderImage',
        'privacyPolicy', 'termsAndConditions', 'maintenance_settings',
        'DriverNearBy', 'DeliveryCharge', 'cashbackOffer', 'story',
        'googleMapKey', 'notification_setting', 'languages', 'AppHomeBanners',
    ];

    public function __construct(protected SettingsService $settingsService)
    {
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

    public function publicSettings(): array
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

    public function languages(): array
    {
        return $this->settingsService->get('languages', []);
    }

    public function deliveryCharge(): ?array
    {
        $row = Setting::query()->find('DeliveryCharge');

        return $row ? ($row->value ?? $row->toDocumentArray()) : null;
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
