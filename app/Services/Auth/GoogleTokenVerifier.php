<?php

namespace App\Services\Auth;

use Google\Client as GoogleClient;
use Illuminate\Validation\ValidationException;

class GoogleTokenVerifier
{
    public function verify(string $idToken): array
    {
        $clientIds = array_filter(array_map('trim', explode(',', (string) config('services.google.client_ids', ''))));

        if ($clientIds === []) {
            throw ValidationException::withMessages([
                'id_token' => ['Google sign-in is not configured on the server.'],
            ]);
        }

        $client = new GoogleClient();

        foreach ($clientIds as $clientId) {
            $client->setClientId($clientId);
            $payload = $client->verifyIdToken($idToken);

            if (is_array($payload)) {
                return [
                    'sub' => $payload['sub'] ?? null,
                    'email' => $payload['email'] ?? null,
                    'email_verified' => (bool) ($payload['email_verified'] ?? false),
                    'given_name' => $payload['given_name'] ?? null,
                    'family_name' => $payload['family_name'] ?? null,
                    'name' => $payload['name'] ?? null,
                    'picture' => $payload['picture'] ?? null,
                ];
            }
        }

        throw ValidationException::withMessages([
            'id_token' => ['Invalid Google ID token.'],
        ]);
    }
}
