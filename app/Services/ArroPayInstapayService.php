<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ArroPayInstapayService
{
    public function processDisbursement(array $payload): array
    {
        $config = config('services.arropay_instapay');
        $payload = $this->mergeSenderDefaults($payload);
        $response = $this->post((string) $config['process_endpoint'], $payload, (int) $config['request_timeout']);

        if ($response['ok']) {
            $data = (array) $response['body'];

            return [
                'success' => true,
                'data' => $data,
                'status_code' => $response['status_code'],
                'status' => $this->normalizeStatus($data['status'] ?? $data['paymentStatus'] ?? $data['code'] ?? null),
                'refno' => $data['refno'] ?? $data['reference'] ?? ($payload['refno'] ?? null),
            ];
        }

        return [
            'success' => false,
            'status_code' => $response['status_code'],
            'message' => $this->extractErrorMessage($response['body']),
            'errors' => $response['body'],
        ];
    }

    public function checkDisbursement(array $payload): array
    {
        return $this->processDisbursement($payload);
    }

    public function mergeSenderDefaults(array $payload): array
    {
        $config = config('services.arropay_instapay');
        $defaults = [
            'sender_account_name' => $config['sender_account_name'] ?? null,
            'sender_account_number' => $config['sender_account_number'] ?? null,
            'sender_mobile_number' => $config['sender_mobile_number'] ?? null,
            'sender_email' => $config['sender_email'] ?? null,
            'sender_address' => $config['sender_address'] ?? null,
            'sender_barangay' => $config['sender_barangay'] ?? null,
            'sender_city' => $config['sender_city'] ?? null,
            'sender_zipcode' => $config['sender_zipcode'] ?? null,
        ];

        foreach ($defaults as $key => $value) {
            if (($payload[$key] ?? '') === '' && $value !== null && $value !== '') {
                $payload[$key] = $value;
            }
        }

        if (empty($payload['callbackURL']) && !empty($config['callback_url'])) {
            $payload['callbackURL'] = $config['callback_url'];
        }

        return $payload;
    }

    public function normalizeStatus($status): string
    {
        $status = strtoupper((string) $status);
        if (in_array($status, ['SUCCESS', 'SUCCEEDED', 'COMPLETED', '200'], true)) {
            return 'SUCCESS';
        }
        if (in_array($status, ['FAILED', 'FAIL', 'DECLINED', 'ERROR'], true)) {
            return 'FAILED';
        }
        if (in_array($status, ['CANCELLED', 'CANCELED'], true)) {
            return 'CANCELLED';
        }
        return 'PENDING';
    }

    protected function post(string $endpoint, array $payload, int $timeout): array
    {
        $config = config('services.arropay_instapay');
        $baseUrl = rtrim((string) $config['base_url'], '/');
        $url = $baseUrl . '/' . ltrim($endpoint, '/');

        $response = Http::timeout($timeout)
            ->withHeaders([
                'API-KEY' => (string) $config['api_key'],
                'API-SECRET' => (string) $config['api_secret'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->post($url, $payload);

        return [
            'ok' => $response->successful(),
            'status_code' => $response->status(),
            'body' => $response->json() ?? ['message' => $response->body()],
        ];
    }

    protected function extractErrorMessage($body): string
    {
        if (is_array($body)) {
            return (string) ($body['message'] ?? $body['error'] ?? 'Gateway request failed.');
        }
        return 'Gateway request failed.';
    }
}
