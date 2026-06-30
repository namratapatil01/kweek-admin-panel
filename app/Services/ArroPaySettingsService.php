<?php

namespace App\Services;

class ArroPaySettingsService
{
    public function __construct(protected SettingsService $settingsService)
    {
    }

    public function getAuthSettings(): array
    {
        $envSettings = $this->getEnvSettings();
        if (! empty($envSettings['apiKey']) && ! empty($envSettings['apiSecret'])) {
            return $envSettings;
        }

        $stored = $this->settingsService->get('arropay_auth_settings');
        if (is_array($stored) && ! empty($stored['apiKey']) && ! empty($stored['apiSecret'])) {
            return [
                'source' => 'mysql',
                'baseUrl' => $this->normalizeBaseUrl((string) ($stored['baseUrl'] ?? '')),
                'loginEndpoint' => $stored['loginEndpoint'] ?? (string) config('services.arropay_auth.login_endpoint', '/api/v2/auth/login'),
                'apiKey' => (string) $stored['apiKey'],
                'apiSecret' => (string) $stored['apiSecret'],
            ];
        }

        return [
            'source' => is_array($stored) ? 'mysql_incomplete' : 'missing',
            'baseUrl' => (string) config('services.arropay_auth.base_url', 'https://arropay.app'),
            'loginEndpoint' => (string) config('services.arropay_auth.login_endpoint', '/api/v2/auth/login'),
            'apiKey' => '',
            'apiSecret' => '',
        ];
    }

    protected function getEnvSettings(): array
    {
        return [
            'source' => 'env',
            'baseUrl' => (string) config('services.arropay_auth.base_url', 'https://arropay.app'),
            'loginEndpoint' => (string) config('services.arropay_auth.login_endpoint', '/api/v2/auth/login'),
            'apiKey' => (string) config('services.arropay_auth.api_key', ''),
            'apiSecret' => (string) config('services.arropay_auth.api_secret', ''),
        ];
    }

    protected function normalizeBaseUrl(string $baseUrl): string
    {
        $baseUrl = trim($baseUrl);
        if ($baseUrl === '') {
            return (string) config('services.arropay_auth.base_url', 'https://arropay.app');
        }

        if (preg_match('#^(https?://[^/]+)/api/v2/auth/login$#i', $baseUrl, $matches)) {
            return $matches[1];
        }

        if (preg_match('#^(https?://[^/]+)/api/v2$#i', $baseUrl, $matches)) {
            return $matches[1] . '/api/v2';
        }

        return rtrim($baseUrl, '/');
    }
}
