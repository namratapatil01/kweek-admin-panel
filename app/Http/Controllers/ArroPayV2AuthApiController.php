<?php

namespace App\Http\Controllers;

use App\Services\ArroPayAuthService;
use App\Services\DocumentStoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ArroPayV2AuthApiController extends Controller
{
    private const ARROPAY_LOGIN_URL = 'https://arropay.app/api/v2/auth/login';
    private const ARROPAY_MAYA_PAYMENT_INTENT_URL = 'https://arropay.app/api/v2/maya/payment-intent';
    private const ARROPAY_MAYA_PAYMENT_CHECK_URL = 'https://arropay.app/api/v2/maya/payment-check';
    private const ARROPAY_API_KEY = 'ak_507595364a12367f6c65db7cb66ea3c0';
    private const ARROPAY_API_SECRET = 'as_3146c4c1e1b5946b7a0f81909ff4df96';
    private const GATEWAY_SECRET = '1234';

    private const GATEWAY_FIELDS = [
        'apiSecret',
        'apiKey',
        'arropayApiSecret',
        'token',
    ];

    public function __construct(
        protected ArroPayAuthService $authService,
        protected DocumentStoreService $documentStore
    ) {
    }

    /**
     * Proxy endpoint for ArroPay API v2 auth login.
     * Client sends gateway apiSecret. ArroPay credentials are hardcoded below.
     */
    public function login(Request $request)
    {
        $providedSecret = trim((string) (
            $request->input('apiSecret')
            ?? $request->header('X-Gateway-Secret')
            ?? ''
        ));

        if ($providedSecret === '') {
            return response()->json([
                'success' => false,
                'message' => 'apiSecret is required.',
                'hint' => 'Send JSON body: {"apiSecret":"1234"} with Content-Type: application/json',
            ], 422);
        }

        if (!hash_equals(self::GATEWAY_SECRET, $providedSecret)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid gateway apiSecret.',
                'hint' => 'Send the gateway apiSecret (default: 1234).',
            ], 403);
        }

        $apiKey = trim((string) ($request->input('apiKey') ?? self::ARROPAY_API_KEY));
        $apiSecret = trim((string) ($request->input('arropayApiSecret') ?? self::ARROPAY_API_SECRET));

        $result = $this->authService->login(
            self::ARROPAY_LOGIN_URL,
            $apiKey,
            $apiSecret
        );

        if (!($result['success'] ?? false)) {
            $arropayMessage = (string) ($result['message'] ?? 'Authentication failed.');

            return response()->json([
                'success' => false,
                'message' => $arropayMessage,
                'login_url' => $result['login_url'] ?? null,
                'raw' => $result['raw'] ?? null,
            ], (int) ($result['status_code'] ?? 502));
        }

        return response()->json($result['data'], 200);
    }

    /**
     * Proxy Maya payment-intent through the server so ArroPay sees a whitelisted IP.
     */
    public function mayaPaymentIntent(Request $request)
    {
        $gatewayError = $this->validateGatewaySecret($request);
        if ($gatewayError !== null) {
            return $gatewayError;
        }

        $token = trim((string) ($request->input('token') ?? $request->bearerToken() ?? ''));
        if ($token === '') {
            $token = $this->resolveArroPayToken($request);
            if ($token === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to obtain ArroPay auth token.',
                ], 502);
            }
        }

        $payload = $request->except(self::GATEWAY_FIELDS);
        $result = $this->authService->proxyAuthenticatedRequest(
            'POST',
            self::ARROPAY_MAYA_PAYMENT_INTENT_URL,
            $token,
            $payload
        );

        if (!($result['success'] ?? false)) {
            Log::warning('ArroPay Maya payment intent failed.', [
                'endpoint' => self::ARROPAY_MAYA_PAYMENT_INTENT_URL,
                'message' => (string) ($result['message'] ?? 'Payment intent request failed.'),
                'status_code' => (int) ($result['status_code'] ?? 502),
                'request_payload' => $payload,
                'raw' => $result['raw'] ?? null,
            ]);

            $this->savePaymentRecord(
                flow: 'payment-intent',
                requestPayload: $payload,
                responseData: is_array($result['raw'] ?? null) ? $result['raw'] : null,
                success: false,
                status: 'FAILED',
                redirectUrl: null,
                message: (string) ($result['message'] ?? 'Payment intent request failed.')
            );

            return response()->json([
                'success' => false,
                'message' => (string) ($result['message'] ?? 'Payment intent request failed.'),
                'raw' => $result['raw'] ?? null,
            ], (int) ($result['status_code'] ?? 502));
        }

        $redirectUrl = $this->extractRedirectUrl($result['data'] ?? []);
        Log::info('ArroPay Maya payment intent success.', [
            'endpoint' => self::ARROPAY_MAYA_PAYMENT_INTENT_URL,
            'status_code' => 200,
            'request_payload' => $payload,
            'redirect_url' => $redirectUrl,
        ]);

        if ($redirectUrl !== null) {
            Log::info('ArroPay payment success redirect URL detected.', [
                'redirect_url' => $redirectUrl,
            ]);
        }

        $this->savePaymentRecord(
            flow: 'payment-intent',
            requestPayload: $payload,
            responseData: is_array($result['data'] ?? null) ? $result['data'] : null,
            success: true,
            status: 'PENDING',
            redirectUrl: $redirectUrl,
            message: null
        );

        return response()->json($result['data'], 200);
    }

    /**
     * Proxy Maya payment-check through the server so ArroPay sees a whitelisted IP.
     */
    public function mayaPaymentCheck(Request $request)
    {
        $gatewayError = $this->validateGatewaySecret($request);
        if ($gatewayError !== null) {
            return $gatewayError;
        }

        $token = trim((string) ($request->input('token') ?? $request->bearerToken() ?? ''));
        if ($token === '') {
            $token = $this->resolveArroPayToken($request);
            if ($token === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to obtain ArroPay auth token.',
                ], 502);
            }
        }

        $payload = $request->except(self::GATEWAY_FIELDS);
        $result = $this->authService->proxyAuthenticatedRequest(
            'POST',
            self::ARROPAY_MAYA_PAYMENT_CHECK_URL,
            $token,
            $payload
        );

        if (!($result['success'] ?? false)) {
            Log::warning('ArroPay Maya payment check failed.', [
                'endpoint' => self::ARROPAY_MAYA_PAYMENT_CHECK_URL,
                'message' => (string) ($result['message'] ?? 'Payment check request failed.'),
                'status_code' => (int) ($result['status_code'] ?? 502),
                'request_payload' => $payload,
                'raw' => $result['raw'] ?? null,
            ]);

            $this->savePaymentRecord(
                flow: 'payment-check',
                requestPayload: $payload,
                responseData: is_array($result['raw'] ?? null) ? $result['raw'] : null,
                success: false,
                status: 'FAILED',
                redirectUrl: null,
                message: (string) ($result['message'] ?? 'Payment check request failed.')
            );

            return response()->json([
                'success' => false,
                'message' => (string) ($result['message'] ?? 'Payment check request failed.'),
                'raw' => $result['raw'] ?? null,
            ], (int) ($result['status_code'] ?? 502));
        }

        $responseData = $result['data'] ?? [];
        $paymentStatus = $this->extractPaymentStatus($responseData);
        $redirectUrl = $this->extractRedirectUrl($responseData);

        Log::info('ArroPay Maya payment check success.', [
            'endpoint' => self::ARROPAY_MAYA_PAYMENT_CHECK_URL,
            'status_code' => 200,
            'request_payload' => $payload,
            'payment_status' => $paymentStatus,
            'redirect_url' => $redirectUrl,
        ]);

        if ($paymentStatus !== null) {
            Log::info('ArroPay payment status from payment-check.', [
                'payment_status' => $paymentStatus,
            ]);
        }

        if ($redirectUrl !== null) {
            Log::info('ArroPay payment redirect URL from payment-check.', [
                'redirect_url' => $redirectUrl,
            ]);
        }

        $this->savePaymentRecord(
            flow: 'payment-check',
            requestPayload: $payload,
            responseData: $responseData,
            success: true,
            status: $paymentStatus ?? 'UNKNOWN',
            redirectUrl: $redirectUrl,
            message: null
        );

        return response()->json($result['data'], 200);
    }

    private function validateGatewaySecret(Request $request): ?JsonResponse
    {
        $providedSecret = trim((string) (
            $request->input('apiSecret')
            ?? $request->header('X-Gateway-Secret')
            ?? ''
        ));

        if ($providedSecret === '') {
            return response()->json([
                'success' => false,
                'message' => 'apiSecret is required.',
                'hint' => 'Send JSON body: {"apiSecret":"1234", ...} with Content-Type: application/json',
            ], 422);
        }

        if (!hash_equals(self::GATEWAY_SECRET, $providedSecret)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid gateway apiSecret.',
                'hint' => 'Send the gateway apiSecret (default: 1234).',
            ], 403);
        }

        return null;
    }

    private function resolveArroPayToken(Request $request): ?string
    {
        $apiKey = trim((string) ($request->input('apiKey') ?? self::ARROPAY_API_KEY));
        $apiSecret = trim((string) ($request->input('arropayApiSecret') ?? self::ARROPAY_API_SECRET));

        $loginResult = $this->authService->login(
            self::ARROPAY_LOGIN_URL,
            $apiKey,
            $apiSecret
        );

        if (!($loginResult['success'] ?? false)) {
            return null;
        }

        return is_string($loginResult['token'] ?? null) ? $loginResult['token'] : null;
    }

    private function extractRedirectUrl(mixed $data): ?string
    {
        if (!is_array($data)) {
            return null;
        }

        $possibleKeys = [
            'redirect_url',
            'redirectUrl',
            'payment_url',
            'paymentUrl',
            'checkout_url',
            'checkoutUrl',
            'url',
            'link',
        ];

        foreach ($possibleKeys as $key) {
            if (isset($data[$key]) && is_string($data[$key]) && trim($data[$key]) !== '') {
                return trim($data[$key]);
            }
        }

        if (isset($data['data']) && is_array($data['data'])) {
            return $this->extractRedirectUrl($data['data']);
        }

        return null;
    }

    private function extractPaymentStatus(mixed $data): ?string
    {
        if (!is_array($data)) {
            return null;
        }

        $possibleKeys = [
            'status',
            'payment_status',
            'paymentStatus',
            'transaction_status',
            'transactionStatus',
        ];

        foreach ($possibleKeys as $key) {
            if (isset($data[$key]) && is_string($data[$key]) && trim($data[$key]) !== '') {
                return trim($data[$key]);
            }
        }

        if (isset($data['data']) && is_array($data['data'])) {
            return $this->extractPaymentStatus($data['data']);
        }

        return null;
    }

    private function savePaymentRecord(
        string $flow,
        array $requestPayload,
        ?array $responseData,
        bool $success,
        string $status,
        ?string $redirectUrl,
        ?string $message
    ): void {
        try {
            $paymentId = $this->extractPaymentReference($requestPayload, $responseData);
            $documentId = $this->resolveDocumentId($requestPayload, $responseData, $paymentId);

            $record = [
                'gateway' => 'arropay_maya_v2',
                'flow' => $flow,
                'success' => $success,
                'status' => strtoupper($status),
                'message' => $message,
                'order_id' => $this->extractScalarValue($requestPayload, ['order_id', 'orderId']),
                'payment_id' => $paymentId,
                'refno' => $this->extractScalarValue($requestPayload, ['refno', 'refNo', 'reference', 'referenceNo']),
                'amount' => $this->extractScalarValue($requestPayload, ['amount', 'total', 'totalAmount']),
                'email' => $this->extractScalarValue($requestPayload, ['email']),
                'firstname' => $this->extractScalarValue($requestPayload, ['firstname', 'firstName']),
                'lastname' => $this->extractScalarValue($requestPayload, ['lastname', 'lastName']),
                'redirect_url' => $redirectUrl,
                'redirect_success' => $this->extractScalarValue($requestPayload, ['redirectSuccess', 'redirect_success']),
                'redirect_failure' => $this->extractScalarValue($requestPayload, ['redirectFailure', 'redirect_failure']),
                'redirect_cancel' => $this->extractScalarValue($requestPayload, ['redirectCancel', 'redirect_cancel']),
                'request_payload' => $requestPayload,
                'response_data' => $responseData,
            ];

            $collection = (string) config('services.arropay_auth.payments_table', 'arropay_v2_payments');

            $result = $this->documentStore->upsertDocument($collection, $documentId, $record);
            if (!($result['success'] ?? false)) {
                Log::warning('Unable to save ArroPay payment details to MySQL.', [
                    'flow' => $flow,
                    'message' => $result['message'] ?? 'MySQL write failed.',
                    'details' => $result['details'] ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Unable to save ArroPay payment details to MySQL.', [
                'flow' => $flow,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function resolveDocumentId(array $requestPayload, ?array $responseData, ?string $paymentId): string
    {
        $candidates = [
            $paymentId,
            $this->extractScalarValue($requestPayload, ['refno', 'refNo', 'reference', 'referenceNo']),
            $this->extractScalarValue($requestPayload, ['order_id', 'orderId']),
            $this->extractScalarValue($responseData ?? [], ['payment_id', 'paymentId', 'checkoutId', 'id']),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return $this->sanitizeDocumentId($candidate);
            }
        }

        return 'payment_' . md5(json_encode([
            'request' => $requestPayload,
            'response' => $responseData,
            'time' => now()->toIso8601String(),
        ]));
    }

    private function sanitizeDocumentId(string $value): string
    {
        $sanitized = preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($value));

        return is_string($sanitized) && $sanitized !== '' ? $sanitized : 'payment_' . uniqid();
    }

    private function extractPaymentReference(array $requestPayload, ?array $responseData): ?string
    {
        $fromRequest = $this->extractScalarValue($requestPayload, [
            'payment_id',
            'paymentId',
            'refno',
            'refNo',
            'reference',
            'referenceNo',
            'checkoutId',
        ]);

        if ($fromRequest !== null) {
            return $fromRequest;
        }

        if ($responseData === null) {
            return null;
        }

        return $this->extractScalarValue($responseData, [
            'payment_id',
            'paymentId',
            'refno',
            'refNo',
            'reference',
            'referenceNo',
            'checkoutId',
            'id',
        ]) ?? $this->extractScalarValue(is_array($responseData['data'] ?? null) ? $responseData['data'] : [], [
            'payment_id',
            'paymentId',
            'refno',
            'refNo',
            'reference',
            'referenceNo',
            'checkoutId',
            'id',
        ]);
    }

    private function extractScalarValue(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $value = $data[$key];
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }

            if (is_int($value) || is_float($value)) {
                return (string) $value;
            }
        }

        return null;
    }
}
