<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseFirestoreService
{
    public function upsertDocument(string $collection, string $documentId, array $data): array
    {
        $credentialsPath = $this->resolveCredentialsPath();
        if (!is_readable($credentialsPath)) {
            return $this->upsertFailure(
                'Firebase credentials file not found.',
                [
                    'credentials_path' => $credentialsPath,
                    'hint' => 'Set FIREBASE_CREDENTIALS=/var/www/adminpanel/storage/app/firebase/credentials.json on the server.',
                ]
            );
        }

        $projectId = $this->resolveProjectId($credentialsPath);
        if ($projectId === '') {
            return $this->upsertFailure(
                'FIREBASE_PROJECT_ID is not configured.',
                [
                    'configured_project_id' => trim((string) config('services.firebase.project_id', '')),
                    'config_cached' => file_exists(base_path('bootstrap/cache/config.php')),
                    'credentials_path' => $credentialsPath,
                    'credentials_readable' => is_readable($credentialsPath),
                    'hint' => 'Set FIREBASE_PROJECT_ID in server .env, then run: php artisan config:clear. If you use config:cache, run it again after updating .env.',
                ]
            );
        }

        try {
            $accessToken = $this->getAccessToken($credentialsPath);
            if ($accessToken === null) {
                return $this->upsertFailure(
                    'Unable to obtain Firebase access token from service account.',
                    ['credentials_path' => $credentialsPath]
                );
            }

            $fields = $this->encodeFields($data);
            $updateMask = implode('&', array_map(
                static fn (string $path) => 'updateMask.fieldPaths=' . rawurlencode($path),
                array_keys($fields)
            ));

            $documentPath = $this->buildDocumentPath($collection, $documentId);
            $patchUrl = sprintf(
                'https://firestore.googleapis.com/v1/projects/%s/databases/(default)/documents/%s?%s',
                $projectId,
                $documentPath,
                $updateMask
            );

            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->patch($patchUrl, ['fields' => $fields]);

            if ($response->successful()) {
                return ['success' => true];
            }

            if ($response->status() === 404) {
                $createUrl = sprintf(
                    'https://firestore.googleapis.com/v1/projects/%s/databases/(default)/documents/%s?documentId=%s',
                    $projectId,
                    rawurlencode($collection),
                    rawurlencode($documentId)
                );

                $createResponse = Http::withToken($accessToken)
                    ->acceptJson()
                    ->post($createUrl, ['fields' => $fields]);

                if ($createResponse->successful()) {
                    return ['success' => true];
                }

                $response = $createResponse;
            }

            $responseBody = $response->json();
            $message = is_array($responseBody)
                ? (string) ($responseBody['error']['message'] ?? 'Firestore write failed.')
                : 'Firestore write failed.';

            Log::warning('Failed to save document to Firebase Firestore.', [
                'collection' => $collection,
                'document_id' => $documentId,
                'status_code' => $response->status(),
                'response' => $responseBody,
            ]);

            return $this->upsertFailure($message, [
                'collection' => $collection,
                'document_id' => $documentId,
                'project_id' => $projectId,
                'credentials_path' => $credentialsPath,
                'status_code' => $response->status(),
                'response' => $responseBody,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Firestore document upsert error', [
                'collection' => $collection,
                'document_id' => $documentId,
                'error' => $e->getMessage(),
            ]);

            return $this->upsertFailure($e->getMessage());
        }
    }

    protected function upsertFailure(string $message, array $details = []): array
    {
        Log::warning('Firestore upsert failed', array_merge(['message' => $message], $details));

        return [
            'success' => false,
            'message' => $message,
            'details' => $details ?: null,
        ];
    }

    protected function buildDocumentPath(string $collection, string $documentId): string
    {
        return rawurlencode($collection) . '/' . rawurlencode($documentId);
    }

    public function getDocument(string $collection, string $documentId): ?array
    {
        $credentialsPath = $this->resolveCredentialsPath();
        if (!is_readable($credentialsPath)) {
            return null;
        }

        $projectId = $this->resolveProjectId();
        if ($projectId === '') {
            return null;
        }

        try {
            $accessToken = $this->getAccessToken($credentialsPath);
            if ($accessToken === null) {
                return null;
            }

            $url = sprintf(
                'https://firestore.googleapis.com/v1/projects/%s/databases/(default)/documents/%s',
                $projectId,
                $this->buildDocumentPath($collection, $documentId)
            );

            $response = Http::withToken($accessToken)->acceptJson()->get($url);
            if ($response->status() === 404) {
                return null;
            }

            if (!$response->successful()) {
                Log::warning('Firestore document fetch failed', [
                    'collection' => $collection,
                    'document_id' => $documentId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            return $this->decodeDocumentFields((array) $response->json('fields', []));
        } catch (\Throwable $e) {
            Log::warning('Firestore document fetch error', [
                'collection' => $collection,
                'document_id' => $documentId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  array<int, array{field: string, op: string, value: mixed}>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function queryDocuments(string $collection, array $filters, int $limit = 10, bool $orderByCreatedAt = true): array
    {
        $credentialsPath = $this->resolveCredentialsPath();
        if (!is_readable($credentialsPath)) {
            return [];
        }

        $projectId = $this->resolveProjectId();
        if ($projectId === '') {
            return [];
        }

        try {
            $accessToken = $this->getAccessToken($credentialsPath);
            if ($accessToken === null) {
                return [];
            }

            $url = sprintf(
                'https://firestore.googleapis.com/v1/projects/%s/databases/(default)/documents:runQuery',
                $projectId
            );

            $structuredFilters = [];
            foreach ($filters as $filter) {
                $structuredFilters[] = [
                    'fieldFilter' => [
                        'field' => ['fieldPath' => (string) $filter['field']],
                        'op' => (string) $filter['op'],
                        'value' => $this->encodeValue($filter['value']),
                    ],
                ];
            }

            $where = count($structuredFilters) === 1
                ? $structuredFilters[0]
                : [
                    'compositeFilter' => [
                        'op' => 'AND',
                        'filters' => $structuredFilters,
                    ],
                ];

            $structuredQuery = [
                'from' => [['collectionId' => $collection]],
                'where' => $where,
                'limit' => $limit,
            ];

            if ($orderByCreatedAt) {
                $structuredQuery['orderBy'] = [[
                    'field' => ['fieldPath' => 'created_at'],
                    'direction' => 'DESCENDING',
                ]];
            }

            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->post($url, [
                    'structuredQuery' => $structuredQuery,
                ]);

            if (!$response->successful()) {
                Log::warning('Firestore query failed', [
                    'collection' => $collection,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [];
            }

            $documents = [];
            foreach ((array) $response->json() as $row) {
                if (!is_array($row) || empty($row['document']['fields'])) {
                    continue;
                }

                $documents[] = $this->decodeDocumentFields((array) $row['document']['fields']);
            }

            return $documents;
        } catch (\Throwable $e) {
            Log::warning('Firestore query error', [
                'collection' => $collection,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    protected function resolveCredentialsPath(): string
    {
        return (string) config(
            'services.firebase.credentials',
            storage_path('app/firebase/credentials.json')
        );
    }

    protected function resolveProjectId(?string $credentialsPath = null): string
    {
        $projectId = trim((string) config('services.firebase.project_id', ''));
        if ($projectId !== '') {
            return $projectId;
        }

        $credentialsPath = $credentialsPath ?: $this->resolveCredentialsPath();
        if (!is_readable($credentialsPath)) {
            return '';
        }

        $credentials = json_decode((string) file_get_contents($credentialsPath), true);

        return is_array($credentials)
            ? trim((string) ($credentials['project_id'] ?? ''))
            : '';
    }

    protected function getAccessToken(string $credentialsPath): ?string
    {
        $client = new GoogleClient();
        $client->setAuthConfig($credentialsPath);
        $client->addScope('https://www.googleapis.com/auth/datastore');

        $token = $client->fetchAccessTokenWithAssertion();

        return is_array($token) ? ($token['access_token'] ?? null) : null;
    }

    protected function encodeFields(array $data): array
    {
        $encoded = [];

        foreach ($data as $key => $value) {
            $encoded[(string) $key] = $this->encodeValue($value);
        }

        return $encoded;
    }

    protected function encodeValue(mixed $value): array
    {
        if ($value === null) {
            return ['nullValue' => null];
        }

        if (is_bool($value)) {
            return ['booleanValue' => $value];
        }

        if (is_int($value)) {
            return ['integerValue' => (string) $value];
        }

        if (is_float($value)) {
            return ['doubleValue' => $value];
        }

        if (is_string($value)) {
            return ['stringValue' => $value];
        }

        if (is_array($value)) {
            if ($value === [] || array_keys($value) === range(0, count($value) - 1)) {
                return [
                    'arrayValue' => [
                        'values' => array_map(fn (mixed $item) => $this->encodeValue($item), $value),
                    ],
                ];
            }

            $fields = [];
            foreach ($value as $key => $item) {
                $fields[(string) $key] = $this->encodeValue($item);
            }

            return ['mapValue' => ['fields' => $fields]];
        }

        return ['stringValue' => (string) $value];
    }

    protected function decodeDocumentFields(array $fields): array
    {
        $parsed = [];

        foreach ($fields as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            $parsed[(string) $key] = $this->decodeValue($value);
        }

        return $parsed;
    }

    protected function decodeValue(array $value): mixed
    {
        if (array_key_exists('stringValue', $value)) {
            return $value['stringValue'];
        }

        if (array_key_exists('booleanValue', $value)) {
            return (bool) $value['booleanValue'];
        }

        if (array_key_exists('integerValue', $value)) {
            return (int) $value['integerValue'];
        }

        if (array_key_exists('doubleValue', $value)) {
            return (float) $value['doubleValue'];
        }

        if (array_key_exists('nullValue', $value)) {
            return null;
        }

        if (array_key_exists('arrayValue', $value)) {
            $items = [];
            foreach ((array) ($value['arrayValue']['values'] ?? []) as $item) {
                if (is_array($item)) {
                    $items[] = $this->decodeValue($item);
                }
            }

            return $items;
        }

        if (array_key_exists('mapValue', $value)) {
            return $this->decodeDocumentFields((array) ($value['mapValue']['fields'] ?? []));
        }

        return null;
    }
}
