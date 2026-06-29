<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirestoreSettingsService
{
    public function getDocument(string $collection, string $documentId): ?array
    {
        $credentialsPath = (string) config('services.firebase.credentials', storage_path('app/firebase/credentials.json'));
        if (!is_readable($credentialsPath)) {
            Log::warning('Firestore credentials file not found', ['path' => $credentialsPath]);
            return null;
        }

        $projectId = (string) config('services.firebase.project_id', '');
        if ($projectId === '') {
            Log::warning('Firebase project ID is not configured');
            return null;
        }

        try {
            $accessToken = $this->getAccessToken($credentialsPath);
            if ($accessToken === null) {
                return null;
            }

            $url = sprintf(
                'https://firestore.googleapis.com/v1/projects/%s/databases/(default)/documents/%s/%s',
                $projectId,
                $collection,
                $documentId
            );

            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->get($url);

            if ($response->status() === 404) {
                Log::warning('Firestore document not found', [
                    'collection' => $collection,
                    'document' => $documentId,
                ]);
                return null;
            }

            if (!$response->successful()) {
                Log::warning('Firestore document fetch failed', [
                    'collection' => $collection,
                    'document' => $documentId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $this->parseDocumentFields((array) $response->json('fields', []));
        } catch (\Throwable $e) {
            Log::warning('Firestore document fetch error', [
                'collection' => $collection,
                'document' => $documentId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    protected function getAccessToken(string $credentialsPath): ?string
    {
        $client = new GoogleClient();
        $client->setAuthConfig($credentialsPath);
        $client->addScope('https://www.googleapis.com/auth/datastore');

        $token = $client->fetchAccessTokenWithAssertion();

        return is_array($token) ? ($token['access_token'] ?? null) : null;
    }

    protected function parseDocumentFields(array $fields): array
    {
        $parsed = [];

        foreach ($fields as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            if (array_key_exists('stringValue', $value)) {
                $parsed[$key] = $value['stringValue'];
                continue;
            }

            if (array_key_exists('booleanValue', $value)) {
                $parsed[$key] = (bool) $value['booleanValue'];
                continue;
            }

            if (array_key_exists('integerValue', $value)) {
                $parsed[$key] = (int) $value['integerValue'];
                continue;
            }

            if (array_key_exists('doubleValue', $value)) {
                $parsed[$key] = (float) $value['doubleValue'];
                continue;
            }

            if (array_key_exists('nullValue', $value)) {
                $parsed[$key] = null;
            }
        }

        return $parsed;
    }
}
