<?php

namespace App\Services\Worker;

use App\Models\Setting;
use App\Services\SettingsService;

class WorkerSettingsService
{
    protected array $publicSettingKeys = [
        'globalSettings',
        'Version',
        'placeHolderImage',
        'privacyPolicy',
        'termsAndConditions',
        'googleMapKey',
        'notification_setting',
        'languages',
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

    public function languages(): array
    {
        return $this->settingsService->get('languages', []);
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
