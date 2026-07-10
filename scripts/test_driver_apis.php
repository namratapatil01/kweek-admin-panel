<?php

/**
 * Smoke-test all /api/driver endpoints — flags 5xx and unexpected exceptions.
 * Run: php scripts/test_driver_apis.php
 */

$baseUrl = getenv('API_BASE_URL') ?: 'http://127.0.0.1:8000';
$sectionId = '6285dcf511651';
$vendorId = '0btqvc3WonrJH9abzWCq';
$orderId = '00442e7a-e92e-488d-8afa-65b08606c768';
$notificationId = 'jdQ4asGWt79NPdHcset1';

$results = ['pass' => [], 'fail' => [], 'skip' => []];

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

    usleep(100000);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $json = null;
    if ($response !== false && $response !== '') {
        $json = json_decode($response, true);
    }

    return [
        'code' => $httpCode,
        'body' => $response,
        'json' => $json,
        'curl_error' => $error ?: null,
    ];
}

function isServerError(array $res): bool
{
    if ($res['curl_error']) {
        return true;
    }
    if ($res['code'] >= 500) {
        return true;
    }
    if (is_array($res['json']) && isset($res['json']['exception'])) {
        return true;
    }

    return false;
}

function record(array &$results, string $name, array $res, array $allowedCodes = [200, 201, 204]): void
{
    $entry = [
        'name' => $name,
        'code' => $res['code'],
        'message' => $res['json']['message'] ?? ($res['curl_error'] ?: substr((string) $res['body'], 0, 200)),
    ];

    if (isServerError($res)) {
        $results['fail'][] = $entry;
    } elseif (in_array($res['code'], $allowedCodes, true) || in_array($res['code'], [401, 403, 404, 409, 422, 429], true)) {
        $results['pass'][] = $entry;
    } else {
        $results['fail'][] = $entry;
    }
}

echo "=== KWEEK Driver API Smoke Test ===\n";
echo "Base URL: {$baseUrl}\n\n";

$health = request('GET', $baseUrl . '/api/driver/home');
if ($health['curl_error'] || $health['code'] === 0) {
    echo "ERROR: Server not reachable at {$baseUrl}\n";
    echo "Start with: php artisan serve --host=127.0.0.1 --port=8000\n";
    exit(1);
}

$ts = time();
$email = 'driverapitest' . $ts . '@kweek.test';
$phone = '77' . substr((string) $ts, -8);

$register = request('POST', $baseUrl . '/api/driver/register', null, [
    'firstName' => 'API',
    'lastName' => 'Driver',
    'email' => $email,
    'phoneNumber' => $phone,
    'countryCode' => '+91',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'serviceType' => 'delivery-service',
    'sectionId' => $sectionId,
    'carName' => 'Swift',
    'carNumber' => 'KA01AB' . substr((string) $ts, -4),
]);

$token = $register['json']['token'] ?? null;

if (! $token) {
    $login = request('POST', $baseUrl . '/api/driver/login', null, [
        'email' => $email,
        'password' => 'password123',
    ]);
    $token = $login['json']['token'] ?? null;
}

if (! $token) {
    $phoneLogin = request('POST', $baseUrl . '/api/driver/auth/phone', null, [
        'phoneNumber' => $phone,
        'countryCode' => '+91',
        'serviceType' => 'delivery-service',
    ]);
    $token = $phoneLogin['json']['token'] ?? null;
}

if (! $token) {
    echo "ERROR: Could not obtain driver auth token\n";
    exit(1);
}

echo "Auth token obtained.\n\n";

$pub = function (string $m, string $path, ?array $body = null, array $codes = [200, 201]) use (&$results, $baseUrl) {
    record($results, "{$m} {$path}", request($m, $baseUrl . $path, null, $body), $codes);
};

$auth = function (string $m, string $path, ?array $body = null, array $codes = [200, 201]) use (&$results, $baseUrl, $token) {
    record($results, "{$m} {$path}", request($m, $baseUrl . $path, $token, $body), $codes);
};

// Public
$pub('GET', '/api/driver/home?serviceType=delivery-service');
$pub('GET', '/api/driver/catalog');
$pub('GET', '/api/driver/catalog?serviceType=delivery-service');
$pub('GET', '/api/driver/settings');
$pub('GET', '/api/driver/settings/payment');
$pub('GET', '/api/driver/settings/languages');
$pub('GET', '/api/driver/settings/taxes?country=India');
$pub('GET', '/api/driver/settings/DriverNearBy');
$pub('GET', '/api/driver/catalog/vendor/' . $vendorId, null, [200, 404]);
$pub('GET', '/api/driver/terms');
$pub('GET', '/api/driver/privacy');
$pub('POST', '/api/driver/password/forgot', ['email' => $email], [200, 422]);
$pub('POST', '/api/driver/auth/google', ['id_token' => 'invalid'], [401, 422]);
$pub('POST', '/api/driver/auth/apple', ['id_token' => 'invalid'], [401, 422]);
$pub('POST', '/api/driver/auth/phone/send-otp', ['phoneNumber' => $phone, 'countryCode' => '+91'], [200, 201]);
$pub('POST', '/api/driver/auth/phone/verify-otp', [
    'verificationId' => 'invalid',
    'otp' => '000000',
    'phoneNumber' => $phone,
    'countryCode' => '+91',
], [401, 422]);

// Authenticated reads
$auth('GET', '/api/driver/profile');
$auth('GET', '/api/driver/dashboard');
$auth('GET', '/api/driver/orders?type=vendor&tab=available');
$auth('GET', '/api/driver/orders?type=vendor&tab=active');
$auth('GET', '/api/driver/orders?type=vendor&tab=completed');
$auth('GET', '/api/driver/orders/vendor/' . $orderId, null, [200, 404]);
$auth('GET', '/api/driver/orders/stream?type=vendor&since=2026-01-01T00:00:00Z');
$auth('GET', '/api/driver/orders/parcel/search?latitude=12.9716&longitude=77.5946&radius=10');
$auth('GET', '/api/driver/orders/rental/search?latitude=12.9716&longitude=77.5946&radius=10&sectionId=' . $sectionId);
$auth('GET', '/api/driver/tracking/orders/vendor/' . $orderId, null, [200, 404]);
$auth('GET', '/api/driver/wallet');
$auth('GET', '/api/driver/wallet/transactions');
$auth('GET', '/api/driver/earnings');
$auth('GET', '/api/driver/wallet/payouts');
$auth('GET', '/api/driver/withdraw-method');
$auth('GET', '/api/driver/chat/inbox');
$auth('GET', '/api/driver/chat/' . $orderId . '/messages', null, [200, 404]);
$auth('GET', '/api/driver/chat/restaurant/inbox');
$auth('GET', '/api/driver/chat/restaurant/' . $orderId . '/messages', null, [200, 404]);
$auth('GET', '/api/driver/reviews');
$auth('GET', '/api/driver/reviews/order/' . $orderId, null, [200, 404]);
$auth('GET', '/api/driver/ratings');
$auth('GET', '/api/driver/notifications');
$auth('GET', '/api/driver/notifications/content/driver_completed');
$auth('PATCH', '/api/driver/notifications/' . $notificationId . '/read', null, [200, 404]);
$auth('GET', '/api/driver/documents');
$auth('GET', '/api/driver/documents/status');
$auth('GET', '/api/driver/owner/drivers', null, [200, 403]);
$auth('GET', '/api/driver/owner/dashboard', null, [200, 403]);
$auth('GET', '/api/driver/owner/drivers/locations', null, [200, 403]);

// Authenticated writes (minimal / expected validation)
$auth('PUT', '/api/driver/profile', ['carName' => 'Swift', 'carNumber' => 'KA01TEST']);
$auth('PUT', '/api/driver/availability', ['online' => true]);
$auth('PUT', '/api/driver/location', ['latitude' => 12.9716, 'longitude' => 77.5946, 'rotation' => 90]);
$auth('PUT', '/api/driver/bank-details', ['userBankDetails' => ['accountNumber' => '1234567890', 'bankName' => 'HDFC', 'holderName' => 'API Driver']]);
$auth('PUT', '/api/driver/withdraw-method', ['stripe' => ['accountId' => 'acct_test']]);
$auth('POST', '/api/driver/wallet/topup', ['amount' => 1, 'paymentMethod' => 'stripe', 'paymentStatus' => 'success', 'transactionId' => 'txn_smoke_' . $ts], [200, 201, 422]);
$auth('POST', '/api/driver/wallet/withdraw', ['amount' => 1, 'withdrawMethod' => 'bank', 'note' => 'smoke test'], [200, 201, 422]);
$auth('POST', '/api/driver/orders/vendor/' . $orderId . '/accept', null, [200, 404, 422]);
$auth('POST', '/api/driver/orders/vendor/' . $orderId . '/reject', ['reason' => 'smoke test'], [200, 404, 422]);
$auth('POST', '/api/driver/orders/vendor/' . $orderId . '/start', ['otp' => '1234'], [200, 404, 422]);
$auth('PATCH', '/api/driver/orders/vendor/' . $orderId . '/status', ['status' => 'In Transit', 'otp' => '1234'], [200, 404, 422]);
$auth('POST', '/api/driver/orders/vendor/' . $orderId . '/complete', ['otp' => '1234'], [200, 404, 422]);
$auth('POST', '/api/driver/chat/send', ['orderId' => $orderId, 'message' => 'smoke test', 'messageType' => 'text'], [200, 201, 422]);
$auth('POST', '/api/driver/chat/restaurant/send', ['orderId' => $orderId, 'message' => 'smoke test', 'messageType' => 'text'], [200, 201, 422]);
$auth('POST', '/api/driver/reviews', ['orderId' => $orderId, 'customerId' => 'cust_smoke', 'rating' => 5, 'comment' => 'smoke'], [200, 201, 422]);
$auth('POST', '/api/driver/documents', ['documents' => [['id' => 'doc1', 'frontURL' => 'https://example.com/front.jpg']]], [200, 201, 422]);

// Report
$serverErrors = array_filter($results['fail'], fn ($f) => $f['code'] >= 500 || str_contains($f['message'] ?? '', 'exception'));

echo str_repeat('=', 60) . "\n";
echo "PASSED: " . count($results['pass']) . "\n";
echo "FAILED: " . count($results['fail']) . "\n";
echo "SERVER ERRORS (5xx): " . count($serverErrors) . "\n";
echo str_repeat('=', 60) . "\n\n";

if (count($serverErrors) > 0) {
    echo "=== SERVER ERRORS (must fix) ===\n";
    foreach ($serverErrors as $f) {
        echo "  [{$f['code']}] {$f['name']}\n";
        echo "    {$f['message']}\n\n";
    }
}

$otherFails = array_filter($results['fail'], fn ($f) => ! in_array($f, $serverErrors, true));
if (count($otherFails) > 0) {
    echo "=== OTHER FAILURES ===\n";
    foreach ($otherFails as $f) {
        echo "  [{$f['code']}] {$f['name']} — {$f['message']}\n";
    }
    echo "\n";
}

foreach ($results['pass'] as $p) {
    echo "  OK  [{$p['code']}] {$p['name']}\n";
}
foreach ($results['fail'] as $f) {
    echo "  FAIL[{$f['code']}] {$f['name']}\n";
}

exit(count($serverErrors) > 0 ? 1 : 0);
