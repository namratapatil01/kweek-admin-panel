<?php

namespace App\Services;

class ArroPaySettingsService
{
    public function __construct(protected FirestoreSettingsService $firestoreSettingsService)
    {
    }

    public function getAuthSettings(): array
    {
        $envSettings = $this->getEnvSettings();
        if (!empty($envSettings['apiKey']) && !empty($envSettings['apiSecret'])) {
            return $envSettings;
        }

        $firestoreSettings = $this->firestoreSettingsService->getDocument('settings', 'arropay_auth_settings');

        if (is_array($firestoreSettings) && !empty($firestoreSettings['apiKey']) && !empty($firestoreSettings['apiSecret'])) {
            return [
                'source' => 'firebase',
                'baseUrl' => $this->normalizeBaseUrl((string) ($firestoreSettings['baseUrl'] ?? '')),
                'loginEndpoint' => $firestoreSettings['loginEndpoint'] ?? (string) config('services.arropay_auth.login_endpoint', '/api/v2/auth/login'),
                'apiKey' => (string) $firestoreSettings['apiKey'],
                'apiSecret' => (string) $firestoreSettings['apiSecret'],
            ];
        }

        return [
            'source' => is_array($firestoreSettings) ? 'firebase_incomplete' : 'missing',
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
