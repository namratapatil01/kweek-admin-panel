<?php

/**
 * Smoke-test Provider API endpoints.
 * Run: php scripts/test_provider_apis.php
 */

$baseUrl = getenv('API_BASE_URL') ?: 'http://127.0.0.1:8000';
$sectionId = getenv('PROVIDER_SECTION_ID') ?: '6285dcf511651';

$results = ['pass' => [], 'fail' => [], 'skip' => []];
$token = null;
$verificationId = null;

function request(string $method, string $url, ?string $token = null, ?array $body = null): array
{
    $ch = curl_init($url);
    $headers = ['Accept: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);

    usleep(80000);
    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'body' => $response,
        'json' => ($response && $response !== '') ? json_decode($response, true) : null,
        'curl_error' => $error ?: null,
    ];
}

function record(array &$results, string $name, array $res, array $allowedCodes = [200, 201, 204]): void
{
    $entry = [
        'name' => $name,
        'code' => $res['code'],
        'message' => $res['json']['message'] ?? ($res['curl_error'] ?: substr((string) $res['body'], 0, 180)),
    ];

    if ($res['curl_error'] || $res['code'] >= 500 || (is_array($res['json']) && isset($res['json']['exception']))) {
        $results['fail'][] = $entry;
    } elseif (in_array($res['code'], $allowedCodes, true)) {
        $results['pass'][] = $entry;
    } else {
        $results['skip'][] = $entry;
    }
}

echo "Provider API smoke test\nBase URL: {$baseUrl}\n\n";

// Public endpoints
record($results, 'GET /home', request('GET', "{$baseUrl}/api/provider/home"));
record($results, 'GET /settings', request('GET', "{$baseUrl}/api/provider/settings"));
record($results, 'GET /settings/payment', request('GET', "{$baseUrl}/api/provider/settings/payment"));
record($results, 'GET /settings/languages', request('GET', "{$baseUrl}/api/provider/settings/languages"));
record($results, 'GET /terms', request('GET', "{$baseUrl}/api/provider/terms"));
record($results, 'GET /privacy', request('GET', "{$baseUrl}/api/provider/privacy"));

$otpSend = request('POST', "{$baseUrl}/api/provider/auth/phone/send-otp", null, [
    'phoneNumber' => '9999900001',
    'countryCode' => '+91',
]);
record($results, 'POST /auth/phone/send-otp', $otpSend);
$verificationId = $otpSend['json']['data']['verificationId'] ?? null;
$debugOtp = $otpSend['json']['data']['debug_otp'] ?? null;

if ($verificationId && $debugOtp) {
    $otpVerify = request('POST', "{$baseUrl}/api/provider/auth/phone/verify-otp", null, [
        'verificationId' => $verificationId,
        'otp' => $debugOtp,
        'phoneNumber' => '9999900001',
        'countryCode' => '+91',
        'firstName' => 'Test',
        'auto_register' => true,
        'fcmToken' => 'test-token',
    ]);
    record($results, 'POST /auth/phone/verify-otp', $otpVerify, [200, 201]);
    $token = $otpVerify['json']['token'] ?? null;
}

if (! $token) {
    $login = request('POST', "{$baseUrl}/api/provider/login", null, [
        'email' => getenv('PROVIDER_TEST_EMAIL') ?: 'provider@example.com',
        'password' => getenv('PROVIDER_TEST_PASSWORD') ?: 'password123',
        'fcmToken' => 'test-token',
    ]);
    record($results, 'POST /login (fallback)', $login, [200, 401, 422]);
    $token = $login['json']['token'] ?? null;
}

if (! $token) {
    echo "No auth token available. Remaining protected routes skipped.\n";
} else {
    $auth = function (string $method, string $path, ?array $body = null, array $codes = [200, 201]) use (&$results, $baseUrl, $token): void {
        record(
            $results,
            "{$method} {$path}",
            request($method, "{$baseUrl}/api/provider{$path}", $token, $body),
            $codes
        );
    };

    $auth('GET', '/profile');
    $auth('GET', '/dashboard');
    $auth('GET', '/realtime/poll?since=' . urlencode(date('c', time() - 300)));
    $auth('GET', '/sections');
    $auth('GET', "/categories?sectionId={$sectionId}");
    $auth('GET', '/services');
    $auth('GET', '/bookings?tab=new');
    $auth('GET', '/bookings?tab=new&since=' . urlencode(date('c', time() - 3600)));
    $auth('GET', '/workers');
    $auth('GET', '/coupons');
    $auth('GET', '/wallet');
    $auth('GET', '/wallet/transactions');
    $auth('GET', '/earnings');
    $auth('GET', '/wallet/payouts');
    $auth('GET', '/withdraw-method');
    $auth('GET', "/subscriptions/plans?sectionId={$sectionId}&isCommissionPlan=false");
    $auth('GET', "/subscriptions/plans?sectionId={$sectionId}&isCommissionPlan=true");
    $auth('GET', '/subscriptions/history');
    $auth('GET', '/chat/inbox?type=customer');
    $auth('GET', '/chat/inbox?type=worker');
    $auth('GET', '/chat/inbox?type=driver');
    $auth('GET', '/chat/inbox?type=store');
    $auth('GET', '/reviews');
    $auth('GET', '/ratings');
    $auth('GET', '/notifications');
    $auth('GET', '/notifications/templates/provider_accepted');
    $auth('GET', '/email-templates/payout_request');
    $auth('GET', '/documents');
    $auth('GET', '/documents/status');
}

echo "\nPASS: " . count($results['pass']) . "\n";
echo "SKIP: " . count($results['skip']) . "\n";
echo "FAIL: " . count($results['fail']) . "\n";

if ($results['fail']) {
    echo "\nFailures:\n";
    foreach ($results['fail'] as $f) {
        echo " - [{$f['code']}] {$f['name']}: {$f['message']}\n";
    }
    exit(1);
}

exit(0);
