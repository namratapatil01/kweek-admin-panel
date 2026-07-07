<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class DefaultSettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::query()->updateOrCreate(
            ['id' => 'globalSettings'],
            [
                'value' => [
                    'admin_panel_color' => '#000000',
                    'theme_color' => 'primary',
                    'website_color' => '#1eb01c',
                    'applicationName' => 'KWEEK',
                    'appLogo' => '/images/kweek-logo.png',
                    'defaultCountryCode' => '+63',
                    'isSelfDelivery' => true,
                    'isEnableAdsFeature' => false,
                    'store_panel_color' => '#ff3838',
                    'provider_panel_color' => '#9928eb',
                    'app_customer_color' => '#211612',
                    'app_driver_color' => '#27c57d',
                    'app_store_color' => '#1da4fe',
                ],
            ]
        );

        Setting::query()->updateOrCreate(
            ['id' => 'placeHolderImage'],
            ['value' => ['image' => '']]
        );

        Setting::query()->updateOrCreate(
            ['id' => 'languages'],
            [
                'value' => [
                    'list' => [
                        [
                            'slug' => 'en',
                            'title' => 'English',
                            'isActive' => true,
                            'is_rtl' => false,
                        ],
                    ],
                ],
            ]
        );

        Setting::query()->updateOrCreate(
            ['id' => 'Version'],
            ['value' => ['web_version' => '1.0.0']]
        );

        Setting::query()->updateOrCreate(
            ['id' => 'googleMapKey'],
            ['value' => ['key' => '']]
        );

        Setting::query()->updateOrCreate(
            ['id' => 'DriverNearBy'],
            ['value' => ['driverNearBy' => 10]]
        );
    }
}
