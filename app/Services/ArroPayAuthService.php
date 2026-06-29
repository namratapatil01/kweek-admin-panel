<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ArroPayAuthService
{
    public function login(string $baseUrl, string $apiKey, string $apiSecret, ?string $loginEndpoint = null): array
    {
        $url = $this->resolveLoginUrl($baseUrl, $loginEndpoint);
        $timeout = (int) config('services.arropay_auth.request_timeout', 30);

        $response = Http::timeout($timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->post($url, [
                'apiKey' => $apiKey,
                'apiSecret' => $apiSecret,
            ]);

        $body = $response->json() ?? ['message' => $response->body()];

        if (!$response->successful()) {
            return [
                'success' => false,
                'status_code' => $response->status(),
                'message' => $this->extractErrorMessage($body),
                'errors' => $body,
                'raw' => $body,
                'login_url' => $url,
            ];
        }

        $data = is_array($body) ? $body : [];
        $token = $this->extractToken($data);

        if (empty($token)) {
            return [
                'success' => false,
                'status_code' => 502,
                'message' => 'Login succeeded but no token was returned.',
                'errors' => $data,
            ];
        }

        return [
            'success' => true,
            'status_code' => $response->status(),
            'token' => $token,
            'expires_at' => $this->extractExpiresAt($data),
            'login_url' => $url,
            'data' => $data,
            'raw' => $data,
        ];
    }

    public function resolveLoginUrl(string $baseUrl, ?string $loginEndpoint = null): string
    {
        $baseUrl = rtrim(trim($baseUrl), '/');

        if (preg_match('#/auth/login$#i', $baseUrl)) {
            return $baseUrl;
        }

        $loginEndpoint = trim((string) (
            $loginEndpoint ?: config('services.arropay_auth.login_endpoint', '/api/v2/auth/login')
        ), '/');

        // Fix common wrong paths like /v2/auth/login or auth/login
        if ($loginEndpoint === 'v2/auth/login') {
            $loginEndpoint = 'api/v2/auth/login';
        } elseif ($loginEndpoint === 'auth/login') {
            $loginEndpoint = 'api/v2/auth/login';
        }

        $loginEndpoint = '/' . ltrim($loginEndpoint, '/');

        // Postman style: base = https://arropay.app/api/v2, endpoint = /auth/login
        if (preg_match('#/api/v2$#i', $baseUrl) && preg_match('#/auth/login$#i', $loginEndpoint)) {
            return $baseUrl . '/auth/login';
        }

        if (str_ends_with($baseUrl, '/api/v2') && preg_match('#^/api/v2/auth/login$#i', $loginEndpoint)) {
            return $baseUrl . '/auth/login';
        }

        if (str_ends_with($baseUrl, '/api') && str_starts_with($loginEndpoint, '/api/')) {
            $loginEndpoint = substr($loginEndpoint, 4);
        }

        return $baseUrl . $loginEndpoint;
    }

    protected function extractToken(array $data): ?string
    {
        $candidates = [
            $data['token'] ?? null,
            $data['accessToken'] ?? null,
            $data['access_token'] ?? null,
            is_array($data['data'] ?? null) ? ($data['data']['token'] ?? null) : null,
            is_array($data['data'] ?? null) ? ($data['data']['accessToken'] ?? null) : null,
            is_array($data['data'] ?? null) ? ($data['data']['access_token'] ?? null) : null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    protected function extractExpiresAt(array $data): ?string
    {
        $candidates = [
            $data['expiresAt'] ?? null,
            $data['expires_at'] ?? null,
            $data['tokenExpiresAt'] ?? null,
            is_array($data['data'] ?? null) ? ($data['data']['expiresAt'] ?? null) : null,
            is_array($data['data'] ?? null) ? ($data['data']['expires_at'] ?? null) : null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    protected function extractErrorMessage($body): string
    {
        if (is_array($body)) {
            return (string) ($body['message'] ?? $body['error'] ?? 'Authentication failed.');
        }

        return 'Authentication failed.';
    }
}
