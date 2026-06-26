<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ArroPayDisbursementService
{
    public function __construct(
        protected FirebaseFirestoreService $firestoreService
    ) {
    }

    public function getBanks(string $channel): array
    {
        if ($this->isProxyMode()) {
            return $this->proxyGetBanks($channel);
        }

        $banks = (array) config("services.arropay_disbursement.banks.{$channel}", []);

        return [
            'success' => true,
            'status_code' => 200,
            'channel' => $channel,
            'banks' => $banks,
        ];
    }

    public function initiateBankWithdraw(array $payload): array
    {
        if ($this->isProxyMode()) {
            return $this->proxyInitiateBankWithdraw($payload);
        }

        return $this->localInitiateBankWithdraw($payload);
    }

    public function processBankWithdraw(array $payload): array
    {
        $payload = $this->normalizeWithdrawalPayload($payload);
        $withdrawal = $this->findWithdrawal($payload);

        if ($withdrawal !== null) {
            return $this->localProcessBankWithdraw($payload, $withdrawal);
        }

        if ($this->isProxyMode()) {
            return $this->proxyProcessBankWithdraw($payload);
        }

        return $this->failure(
            404,
            'Initiated disbursement transaction was not found in Firebase Firestore.',
            $this->withdrawalLookupDebug($payload)
        );
    }

    protected function normalizeWithdrawalPayload(array $payload): array
    {
        $transactionId = trim((string) (
            $payload['transactionId']
            ?? $payload['transaction_id']
            ?? ''
        ));
        $orderNumber = trim((string) (
            $payload['orderNumber']
            ?? $payload['order_number']
            ?? ''
        ));

        return array_filter([
            'otp' => isset($payload['otp']) ? trim((string) $payload['otp']) : null,
            'transactionId' => $transactionId !== '' ? $transactionId : null,
            'orderNumber' => $orderNumber !== '' ? $orderNumber : null,
            'channel' => isset($payload['channel']) ? trim((string) $payload['channel']) : null,
        ], static fn ($value) => $value !== null && $value !== '');
    }

    protected function withdrawalLookupDebug(array $payload): array
    {
        $credentialsPath = (string) config(
            'services.firebase.credentials',
            storage_path('app/firebase/credentials.json')
        );

        return [
            'mode' => $this->isProxyMode() ? 'proxy' : 'local',
            'collection' => $this->withdrawalsCollection(),
            'firebase_project_id' => trim((string) config('services.firebase.project_id', '')),
            'firebase_credentials' => $credentialsPath,
            'credentials_readable' => is_readable($credentialsPath),
            'transaction_id' => $payload['transactionId'] ?? null,
            'order_number' => $payload['orderNumber'] ?? null,
            'hint' => 'Set FIREBASE_PROJECT_ID and FIREBASE_CREDENTIALS in server .env, then run php artisan config:clear.',
        ];
    }

    protected function localInitiateBankWithdraw(array $payload): array
    {
        $amount = (float) ($payload['amount'] ?? 0);
        $available = $this->availableWalletBalance();

        if ($amount > $available) {
            return $this->failure(422, 'Insufficient balance.', [
                'available' => $available,
                'requested' => $amount,
            ]);
        }

        $orderNumber = (string) ($payload['orderNumber'] ?? '');
        if ($orderNumber !== '') {
            $existing = $this->findActiveWithdrawalByOrderNumber($orderNumber);
            if ($existing !== null) {
                return $this->failure(422, 'An active withdrawal already exists for this order number.');
            }
        }

        $transactionId = (string) Str::uuid();
        $providerOrderNumber = 'APY-' . strtoupper(Str::random(12));
        $otp = $this->generateOtp();
        $now = now()->toIso8601String();

        $record = [
            'transaction_id' => $transactionId,
            'order_number' => $orderNumber !== '' ? $orderNumber : null,
            'channel' => (string) $payload['channel'],
            'full_name' => (string) $payload['fullName'],
            'phone' => (string) $payload['phone'],
            'account_number' => (string) $payload['accountNumber'],
            'bank_code' => (string) $payload['bankCode'],
            'amount' => $amount,
            'notify_url' => $payload['notifyUrl'] ?? null,
            'status' => 'PENDING',
            'otp_hash' => $this->hashOtp($otp),
            'provider_order_number' => $providerOrderNumber,
            'gateway' => 'arropay_disbursement',
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $saveResult = $this->saveWithdrawal($transactionId, $record);
        if (!($saveResult['success'] ?? false)) {
            return $this->failure(
                500,
                (string) ($saveResult['message'] ?? 'Unable to save disbursement to Firebase Firestore.'),
                is_array($saveResult['details'] ?? null) ? $saveResult['details'] : []
            );
        }

        return [
            'success' => true,
            'status_code' => 200,
            'status' => 'PENDING',
            'message' => 'Disbursement is pending.',
            'channel' => (string) $payload['channel'],
            'order_number' => $orderNumber,
            'provider_order_number' => $providerOrderNumber,
            'transaction_id' => $transactionId,
        ];
    }

    protected function localProcessBankWithdraw(array $payload, ?array $withdrawal = null): array
    {
        $withdrawal ??= $this->findWithdrawal($payload);
        if ($withdrawal === null) {
            return $this->failure(
                404,
                'Initiated disbursement transaction was not found in Firebase Firestore.',
                $this->withdrawalLookupDebug($payload)
            );
        }

        if (($withdrawal['status'] ?? '') !== 'PENDING') {
            return $this->failure(422, 'Withdrawal is no longer pending OTP confirmation.', status: (string) ($withdrawal['status'] ?? 'REJECTED'));
        }

        if (!$this->verifyOtp((string) $payload['otp'], (string) ($withdrawal['otp_hash'] ?? ''))) {
            $this->updateWithdrawalStatus((string) $withdrawal['transaction_id'], 'REJECTED');

            return $this->failure(422, 'Invalid OTP.', status: 'REJECTED');
        }

        $available = $this->availableWalletBalance();
        $withdrawalAmount = (float) ($withdrawal['amount'] ?? 0);
        if ($withdrawalAmount > $available) {
            $this->updateWithdrawalStatus((string) $withdrawal['transaction_id'], 'REJECTED');

            return $this->failure(422, 'Insufficient balance.', [
                'available' => $available,
                'requested' => $withdrawalAmount,
            ], status: 'REJECTED');
        }

        $this->deductWalletBalance($withdrawalAmount);
        $this->updateWithdrawalStatus((string) $withdrawal['transaction_id'], 'PROCESSING');
        $withdrawal['status'] = 'PROCESSING';

        $this->notifyMerchant($withdrawal);

        return [
            'success' => true,
            'status_code' => 200,
            'status' => 'PROCESSING',
            'message' => 'Disbursement is processing.',
            'channel' => (string) ($withdrawal['channel'] ?? ''),
            'order_number' => (string) ($withdrawal['order_number'] ?? ''),
            'provider_order_number' => (string) ($withdrawal['provider_order_number'] ?? ''),
            'transaction_id' => (string) ($withdrawal['transaction_id'] ?? ''),
        ];
    }

    protected function findWithdrawal(array $payload): ?array
    {
        $payload = $this->normalizeWithdrawalPayload($payload);
        $transactionId = (string) ($payload['transactionId'] ?? '');
        $orderNumber = (string) ($payload['orderNumber'] ?? '');

        if ($transactionId !== '') {
            $document = $this->firestoreService->getDocument(
                $this->withdrawalsCollection(),
                $transactionId
            );

            if (is_array($document)) {
                $document['transaction_id'] = (string) ($document['transaction_id'] ?? $transactionId);

                return $document;
            }

            $documents = $this->firestoreService->queryDocuments(
                $this->withdrawalsCollection(),
                [['field' => 'transaction_id', 'op' => 'EQUAL', 'value' => $transactionId]],
                1,
                false
            );

            if (isset($documents[0])) {
                $documents[0]['transaction_id'] = (string) ($documents[0]['transaction_id'] ?? $transactionId);

                return $documents[0];
            }
        }

        if ($orderNumber !== '') {
            $filters = [
                ['field' => 'order_number', 'op' => 'EQUAL', 'value' => $orderNumber],
            ];

            if (!empty($payload['channel'])) {
                $filters[] = ['field' => 'channel', 'op' => 'EQUAL', 'value' => (string) $payload['channel']];
            }

            $documents = $this->firestoreService->queryDocuments(
                $this->withdrawalsCollection(),
                $filters,
                1,
                true
            );

            return $documents[0] ?? null;
        }

        return null;
    }

    protected function findActiveWithdrawalByOrderNumber(string $orderNumber): ?array
    {
        $documents = $this->firestoreService->queryDocuments(
            $this->withdrawalsCollection(),
            [
                ['field' => 'order_number', 'op' => 'EQUAL', 'value' => $orderNumber],
                ['field' => 'status', 'op' => 'IN', 'value' => ['PENDING', 'PROCESSING']],
            ],
            1
        );

        return $documents[0] ?? null;
    }

    protected function saveWithdrawal(string $transactionId, array $record): array
    {
        return $this->firestoreService->upsertDocument(
            $this->withdrawalsCollection(),
            $transactionId,
            $record
        );
    }

    protected function updateWithdrawalStatus(string $transactionId, string $status): bool
    {
        $result = $this->firestoreService->upsertDocument(
            $this->withdrawalsCollection(),
            $transactionId,
            [
                'status' => $status,
                'updated_at' => now()->toIso8601String(),
            ]
        );

        return (bool) ($result['success'] ?? false);
    }

    protected function withdrawalsCollection(): string
    {
        return (string) config(
            'services.arropay_disbursement.firestore_collection',
            'arropay_disbursement_withdrawals'
        );
    }

    protected function availableWalletBalance(): float
    {
        $configured = (float) config('services.arropay_disbursement.source_wallet_balance', 0);
        $deducted = (float) Cache::get('disbursement_wallet_deducted_total', 0);

        return max(0, $configured - $deducted);
    }

    protected function deductWalletBalance(float $amount): void
    {
        $deducted = (float) Cache::get('disbursement_wallet_deducted_total', 0);
        Cache::forever('disbursement_wallet_deducted_total', $deducted + $amount);
    }

    protected function generateOtp(): string
    {
        $fixedOtp = trim((string) config('services.arropay_disbursement.test_otp', ''));

        if ($fixedOtp !== '') {
            return $fixedOtp;
        }

        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    protected function hashOtp(string $otp): string
    {
        return hash('sha256', $otp . '|' . (string) config('app.key'));
    }

    protected function verifyOtp(string $otp, string $hash): bool
    {
        return hash_equals($hash, $this->hashOtp($otp));
    }

    protected function notifyMerchant(array $withdrawal): void
    {
        if (empty($withdrawal['notify_url'])) {
            return;
        }

        try {
            Http::timeout(10)->post((string) $withdrawal['notify_url'], [
                'success' => true,
                'status' => 'PROCESSING',
                'channel' => $withdrawal['channel'] ?? null,
                'order_number' => $withdrawal['order_number'] ?? null,
                'provider_order_number' => $withdrawal['provider_order_number'] ?? null,
                'transaction_id' => $withdrawal['transaction_id'] ?? null,
                'amount' => (float) ($withdrawal['amount'] ?? 0),
            ]);
        } catch (\Throwable $e) {
            // Callback delivery is best-effort.
        }
    }

    protected function isProxyMode(): bool
    {
        return strtolower((string) config('services.arropay_disbursement.mode', 'local')) === 'proxy';
    }

    protected function failure(int $statusCode, string $message, array $errors = [], string $status = 'REJECTED'): array
    {
        return [
            'success' => false,
            'status_code' => $statusCode,
            'message' => $message,
            'errors' => $errors ?: null,
            'status' => $status,
        ];
    }

    protected function proxyGetBanks(string $channel): array
    {
        $config = config('services.arropay_disbursement');
        $response = $this->post((string) $config['banks_endpoint'], ['channel' => $channel], (int) $config['request_timeout']);

        if (!$response['ok']) {
            return $this->mapProxyFailure($response);
        }

        $data = (array) $response['body'];

        return [
            'success' => true,
            'status_code' => $response['status_code'],
            'channel' => $data['channel'] ?? $channel,
            'banks' => $data['banks'] ?? [],
            'data' => $data,
        ];
    }

    protected function proxyInitiateBankWithdraw(array $payload): array
    {
        $config = config('services.arropay_disbursement');
        $response = $this->post((string) $config['initiate_endpoint'], $payload, (int) $config['request_timeout']);

        return $this->mapWithdrawalResult($response, $payload);
    }

    protected function proxyProcessBankWithdraw(array $payload): array
    {
        $config = config('services.arropay_disbursement');
        $response = $this->post((string) $config['process_endpoint'], $payload, (int) $config['request_timeout']);

        return $this->mapWithdrawalResult($response, $payload, 'PROCESSING', 'Disbursement is processing.');
    }

    protected function mapProxyFailure(array $response): array
    {
        $body = $response['body'] ?? [];

        return [
            'success' => false,
            'status_code' => $response['status_code'],
            'message' => $this->extractErrorMessage($body),
            'errors' => $body,
            'status' => $this->normalizeStatus(is_array($body) ? ($body['status'] ?? null) : null, 'REJECTED'),
        ];
    }

    protected function mapWithdrawalResult(array $response, array $payload, string $defaultStatus = 'PENDING', string $defaultMessage = 'Disbursement is pending.'): array
    {
        $data = (array) ($response['body'] ?? []);
        $channel = (string) ($data['channel'] ?? $payload['channel'] ?? '');
        $orderNumber = (string) ($data['order_number'] ?? $data['orderNumber'] ?? $payload['orderNumber'] ?? '');

        if (!$response['ok']) {
            $result = $this->mapProxyFailure($response);
            $result['channel'] = $channel;
            $result['order_number'] = $orderNumber;
            $result['provider_order_number'] = $this->extractField($data, 'provider_order_number', 'providerOrderNumber');
            $result['transaction_id'] = $this->extractField($data, 'transaction_id', 'transactionId')
                ?: (string) ($payload['transactionId'] ?? '');

            return $result;
        }

        $success = !array_key_exists('success', $data) || (bool) $data['success'];
        $status = $this->normalizeStatus($data['status'] ?? null, $defaultStatus);

        return [
            'success' => $success,
            'status_code' => $response['status_code'],
            'status' => $status,
            'message' => (string) ($data['message'] ?? ($success ? $defaultMessage : 'Disbursement was rejected.')),
            'channel' => $channel,
            'order_number' => $orderNumber,
            'provider_order_number' => $this->extractField($data, 'provider_order_number', 'providerOrderNumber'),
            'transaction_id' => $this->extractField($data, 'transaction_id', 'transactionId'),
            'data' => $data,
        ];
    }

    public function normalizeStatus($status, string $default = 'PENDING'): string
    {
        $status = strtoupper((string) $status);
        if ($status === '') {
            return $default;
        }
        if (in_array($status, ['SUCCESS', 'SUCCEEDED', 'COMPLETED'], true)) {
            return 'SUCCESS';
        }
        if (in_array($status, ['FAILED', 'FAIL', 'DECLINED', 'ERROR'], true)) {
            return 'FAILED';
        }
        if ($status === 'REJECTED') {
            return 'REJECTED';
        }
        if (in_array($status, ['PROCESSING', 'IN_PROGRESS'], true)) {
            return 'PROCESSING';
        }
        if ($status === 'PENDING') {
            return 'PENDING';
        }

        return $default;
    }

    protected function extractField(array $data, string $snakeKey, string $camelKey): ?string
    {
        $value = $data[$snakeKey] ?? $data[$camelKey] ?? null;

        return $value === null || $value === '' ? null : (string) $value;
    }

    protected function post(string $endpoint, array $payload, int $timeout): array
    {
        $config = (array) config('services.arropay_disbursement', []);
        $baseUrl = $this->resolveDisbursementBaseUrl($config);
        $url = $baseUrl . '/' . ltrim($endpoint, '/');

        if ($baseUrl === '') {
            return [
                'ok' => false,
                'status_code' => 500,
                'body' => [
                    'message' => 'ARROPAY_DISBURSEMENT_BASE_URL is not configured.',
                    'requested_url' => $url,
                ],
            ];
        }

        $response = Http::timeout($timeout)
            ->withHeaders([
                'API-KEY' => (string) $config['api_key'],
                'API-SECRET' => (string) $config['api_secret'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->post($url, $payload);

        $rawBody = (string) $response->body();
        $jsonBody = $response->json();

        if (!is_array($jsonBody)) {
            if ($this->isHtmlResponse($rawBody)) {
                return [
                    'ok' => false,
                    'status_code' => 502,
                    'body' => [
                        'message' => 'Payment gateway returned an HTML page instead of JSON. Set ARROPAY_DISBURSEMENT_BASE_URL to the ArroPay API host (https://arropay.app), not the web frontend (https://arropay.biz).',
                        'requested_url' => $url,
                        'gateway_status' => $response->status(),
                    ],
                ];
            }

            return [
                'ok' => false,
                'status_code' => $response->successful() ? 502 : $response->status(),
                'body' => ['message' => $rawBody !== '' ? $rawBody : 'Gateway request failed.'],
            ];
        }

        return [
            'ok' => $response->successful(),
            'status_code' => $response->status(),
            'body' => $jsonBody,
        ];
    }

    protected function resolveDisbursementBaseUrl(array $config): string
    {
        $baseUrl = rtrim(trim((string) ($config['base_url'] ?? '')), '/');

        if ($baseUrl === '') {
            $baseUrl = rtrim(trim((string) config('services.arropay_auth.base_url', 'https://arropay.app')), '/');
        }

        $host = strtolower((string) parse_url($baseUrl, PHP_URL_HOST));
        if (in_array($host, ['arropay.biz', 'www.arropay.biz', 'qa.arropay.biz'], true)) {
            return 'https://arropay.app';
        }

        return $baseUrl;
    }

    protected function isHtmlResponse(string $body): bool
    {
        $snippet = strtolower(ltrim($body));

        return str_starts_with($snippet, '<!doctype html')
            || str_starts_with($snippet, '<html')
            || str_contains($snippet, '<title>arropay gateway</title>');
    }

    protected function extractErrorMessage($body): string
    {
        if (!is_array($body)) {
            return 'Gateway request failed.';
        }

        if (!empty($body['message'])) {
            return (string) $body['message'];
        }

        if (!empty($body['error'])) {
            return (string) $body['error'];
        }

        if (isset($body['available'], $body['requested'])) {
            return sprintf(
                'Insufficient balance. Available: %s, Requested: %s',
                $body['available'],
                $body['requested']
            );
        }

        return 'Gateway request failed.';
    }
}
