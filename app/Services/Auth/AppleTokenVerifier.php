<?php

namespace App\Services\Auth;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class AppleTokenVerifier
{
    public function verify(string $identityToken): array
    {
        $clientIds = array_filter(array_map('trim', explode(',', (string) config('services.apple.client_ids', ''))));

        if ($clientIds === []) {
            throw ValidationException::withMessages([
                'id_token' => ['Apple sign-in is not configured on the server.'],
            ]);
        }

        $keys = Cache::remember('apple_sign_in_public_keys', 3600, function () {
            $response = Http::timeout(10)->get('https://appleid.apple.com/auth/keys');

            if (! $response->successful()) {
                throw ValidationException::withMessages([
                    'id_token' => ['Unable to verify Apple ID token.'],
                ]);
            }

            return $response->json();
        });

        try {
            $decoded = JWT::decode($identityToken, JWK::parseKeySet($keys));
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'id_token' => ['Invalid Apple ID token.'],
            ]);
        }

        $claims = (array) $decoded;

        if (($claims['iss'] ?? null) !== 'https://appleid.apple.com') {
            throw ValidationException::withMessages([
                'id_token' => ['Invalid Apple token issuer.'],
            ]);
        }

        if (! in_array($claims['aud'] ?? null, $clientIds, true)) {
            throw ValidationException::withMessages([
                'id_token' => ['Apple token audience is not allowed.'],
            ]);
        }

        return [
            'sub' => $claims['sub'] ?? null,
            'email' => $claims['email'] ?? null,
            'email_verified' => ($claims['email_verified'] ?? 'false') === 'true' || ($claims['email_verified'] ?? false) === true,
        ];
    }
}
