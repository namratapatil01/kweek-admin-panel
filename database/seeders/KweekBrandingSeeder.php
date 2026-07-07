<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Section;
use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Applies KWEEK branding after legacy Firebase/collections import (which may contain eMart data).
 */
class KweekBrandingSeeder extends Seeder
{
    public function run(): void
    {
        $this->applyGlobalSettings();
        $this->applyCurrency();
        $this->applySectionNames();
    }

    protected function applyGlobalSettings(): void
    {
        $existing = Setting::query()->find('globalSettings')?->value ?? [];

        Setting::query()->updateOrCreate(
            ['id' => 'globalSettings'],
            [
                'value' => array_merge($existing, [
                    'applicationName' => 'KWEEK',
                    'appLogo' => '/images/kweek-logo.png',
                    'admin_panel_color' => $existing['admin_panel_color'] ?? '#000000',
                    'theme_color' => $existing['theme_color'] ?? 'primary',
                    'website_color' => $existing['website_color'] ?? '#1eb01c',
                    'defaultCountryCode' => $existing['defaultCountryCode'] ?? '+63',
                ]),
            ]
        );
    }

    protected function applyCurrency(): void
    {
        Currency::query()->update(['isActive' => false]);

        Currency::query()->updateOrCreate(
            ['code' => 'PHP'],
            [
                'id' => 'kweek-php-peso',
                'country' => 'Philippines',
                'name' => 'Philippine Peso',
                'symbol' => '₱',
                'isActive' => true,
                'symbolAtRight' => false,
                'decimal_degits' => 2,
            ]
        );
    }

    protected function applySectionNames(): void
    {
        $renames = [
            'yJTddzJUxP3cOU5DpJ10' => 'Home Services',
        ];

        foreach ($renames as $id => $name) {
            Section::query()->where('id', $id)->update(['name' => $name]);
        }
    }
}
