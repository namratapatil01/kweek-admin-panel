<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public function get(string $key, mixed $default = null): mixed
    {
        if (! config('kweek.cache.enabled', true)) {
            return $this->fetch($key, $default);
        }

        return Cache::remember(
            "kweek.setting.{$key}",
            config('kweek.cache.ttl', 300),
            fn () => $this->fetch($key, $default)
        );
    }

    public function put(string $key, array $value): Setting
    {
        $setting = Setting::query()->updateOrCreate(
            ['id' => $key],
            ['value' => $value]
        );

        Cache::forget("kweek.setting.{$key}");

        return $setting;
    }

    public function forget(string $key): void
    {
        Setting::query()->where('id', $key)->delete();
        Cache::forget("kweek.setting.{$key}");
    }

    protected function fetch(string $key, mixed $default): mixed
    {
        $setting = Setting::query()->find($key);

        return $setting?->value ?? $default;
    }
}
