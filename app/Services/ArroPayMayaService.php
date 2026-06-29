<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ArroPayMayaService
{
    public function createPayment(array $payload, bool $preferCheckout = true): array
    {
        $config = config('services.arropay_maya');
        $endpoint = $preferCheckout ? $config['checkout_endpoint'] : $config['intent_endpoint'];
        $response = $this->post($endpoint, $payload, (int) $config['request_timeout']);

        if ($response['ok']) {
            $data = (array) $response['body'];
            $paymentId = $data['checkoutId'] ?? $data['paymentId'] ?? null;
            $redirectUrl = $data['redirectUrl'] ?? null;

            return [
                'success' => true,
                'data' => $data,
                'payment_id' => $paymentId,
                'redirect_url' => $redirectUrl,
                'status_code' => $response['status_code'],
            ];
        }

        return [
            'success' => false,
            'status_code' => $response['status_code'],
            'message' => $this->extractErrorMessage($response['body']),
            'errors' => $response['body'],
        ];
    }

    public function checkPayment(string $reference): array
    {
        $config = config('services.arropay_maya');
        $response = $this->post(
            $config['check_endpoint'],
            ['refno' => $reference],
            (int) $config['request_timeout']
        );

        if ($response['ok']) {
            $data = (array) $response['body'];
            $normalizedStatus = $this->normalizeStatus($data['status'] ?? $data['paymentStatus'] ?? null);

            return [
                'success' => true,
                'status_code' => $response['status_code'],
                'status' => $normalizedStatus,
                'data' => $data,
            ];
        }

        return [
            'success' => false,
            'status_code' => $response['status_code'],
            'message' => $this->extractErrorMessage($response['body']),
            'errors' => $response['body'],
        ];
    }

    public function normalizeStatus(?string $status): string
    {
        $status = strtoupper((string) $status);
        if (in_array($status, ['SUCCESS', 'SUCCEEDED', 'COMPLETED'], true)) {
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
        $config = config('services.arropay_maya');
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
