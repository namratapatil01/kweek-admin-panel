<?php

/**
 * Smoke-test all /api/vendor endpoints — flags 5xx and unexpected exceptions.
 * Run: php scripts/test_vendor_apis.php
 */

$baseUrl = getenv('API_BASE_URL') ?: 'http://127.0.0.1:8000';
$sectionId = getenv('VENDOR_SECTION_ID') ?: '6285dcf511651';

$results = ['pass' => [], 'fail' => [], 'skip' => []];
$token = null;
$productId = null;
$couponId = null;
$driverId = null;
$advertisementId = null;
$bookingId = 'nonexistent-booking-id';

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
        'message' => $res['json']['message'] ?? ($res['curl_error'] ?: substr((string) $res['body'], 0, 120)),
    ];

    if (isServerError($res)) {
        $results['fail'][] = $entry;
    } elseif (in_array($res['code'], $allowedCodes, true) || in_array($res['code'], [401, 403, 404, 409, 422, 429], true)) {
        $results['pass'][] = $entry;
    } else {
        $results['fail'][] = $entry;
    }
}

echo "=== KWEEK Vendor API Smoke Test ===\n";
echo "Base URL: {$baseUrl}\n\n";

$health = request('GET', $baseUrl . '/api/vendor/home');
if ($health['curl_error'] || $health['code'] === 0) {
    echo "ERROR: Server not reachable at {$baseUrl}\n";
    echo "Start with: php artisan serve --host=127.0.0.1 --port=8000\n";
    exit(1);
}

$ts = time();
$email = 'vendorapitest' . $ts . '@kweek.test';
$phone = '97' . substr((string) $ts, -8);
$orderId = 'smoke-order-' . $ts;

$register = request('POST', $baseUrl . '/api/vendor/register', null, [
    'firstName' => 'API',
    'lastName' => 'Vendor',
    'email' => $email,
    'phoneNumber' => $phone,
    'countryCode' => '+91',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'sectionId' => $sectionId,
]);
record($results, 'POST /register', $register, [200, 201]);
$token = $register['json']['token'] ?? null;

if (! $token) {
    $login = request('POST', $baseUrl . '/api/vendor/login', null, [
        'email' => getenv('VENDOR_TEST_EMAIL') ?: 'vendor@example.com',
        'password' => getenv('VENDOR_TEST_PASSWORD') ?: 'password123',
        'fcmToken' => 'test-token',
    ]);
    record($results, 'POST /login (fallback)', $login, [200, 401, 422]);
    $token = $login['json']['token'] ?? null;
}

if (! $token) {
    echo "ERROR: Could not obtain auth token\n";
    exit(1);
}

echo "Auth token obtained.\n\n";

$pub = function (string $m, string $path, ?array $body = null, array $codes = [200, 201]) use (&$results, $baseUrl) {
    record($results, "{$m} {$path}", request($m, $baseUrl . $path, null, $body), $codes);
};

$auth = function (string $m, string $path, ?array $body = null, array $codes = [200, 201, 204]) use (&$results, $baseUrl, $token) {
    record($results, "{$m} {$path}", request($m, $baseUrl . $path, $token, $body), $codes);
};

// --- Public endpoints ---
$pub('GET', '/api/vendor/home');
$pub('GET', '/api/vendor/terms');
$pub('GET', '/api/vendor/privacy');
$pub('GET', '/api/vendor/catalog');
$pub('GET', '/api/vendor/catalog?sectionId=' . $sectionId);
$pub('GET', '/api/vendor/subscriptions/plans');
$pub('GET', '/api/vendor/subscriptions/plans?sectionId=' . $sectionId);
$pub('POST', '/api/vendor/password/forgot', ['email' => $email], [200, 202]);
$pub('POST', '/api/vendor/password/reset', [
    'email' => $email,
    'token' => 'invalid-token',
    'password' => 'newpassword123',
    'password_confirmation' => 'newpassword123',
], [200, 422]);
$pub('POST', '/api/vendor/auth/google', ['id_token' => 'invalid'], [401, 422]);
$pub('POST', '/api/vendor/auth/apple', ['id_token' => 'invalid'], [401, 422]);
$pub('POST', '/api/vendor/auth/phone', [
    'phoneNumber' => '96' . substr((string) $ts, -8),
    'countryCode' => '+91',
    'auto_register' => true,
    'fcmToken' => 'test-token',
], [200, 201, 422]);

// Unauthorized access check
record($results, 'GET /profile (no token)', request('GET', $baseUrl . '/api/vendor/profile'), [401, 403]);

// --- Authenticated: profile & store ---
$auth('GET', '/api/vendor/profile');
$auth('PUT', '/api/vendor/profile', ['firstName' => 'API', 'lastName' => 'Vendor', 'fcmToken' => 'test-token']);
$auth('PUT', '/api/vendor/bank-details', ['userBankDetails' => [
    'accountNumber' => '1234567890',
    'bankName' => 'Test Bank',
    'holderName' => 'API Vendor',
]]);
$auth('GET', '/api/vendor/store');
$auth('POST', '/api/vendor/store', [
    'title' => 'Smoke Test Store ' . $ts,
    'description' => 'API smoke test store',
    'latitude' => 12.9716,
    'longitude' => 77.5946,
    'isSelfDelivery' => true,
    'dine_in_active' => false,
], [200, 201, 422]);
$auth('PUT', '/api/vendor/store', ['description' => 'Updated via smoke test']);
$auth('GET', '/api/vendor/dashboard');

// --- Orders ---
$auth('GET', '/api/vendor/orders');
$auth('GET', '/api/vendor/orders?tab=new');
$auth('GET', '/api/vendor/orders?tab=active');
$auth('GET', '/api/vendor/orders?tab=completed');
$auth('GET', '/api/vendor/orders?tab=cancelled');
$auth('GET', '/api/vendor/orders/' . $orderId, null, [200, 404]);
$auth('POST', '/api/vendor/orders/' . $orderId . '/accept', null, [200, 404, 422]);
$auth('POST', '/api/vendor/orders/' . $orderId . '/reject', ['reason' => 'Out of stock'], [200, 404, 422]);
$auth('POST', '/api/vendor/orders/' . $orderId . '/cancel', ['reason' => 'Smoke test'], [200, 404, 422]);
$auth('POST', '/api/vendor/orders/' . $orderId . '/complete', null, [200, 404, 422]);
$auth('POST', '/api/vendor/orders/' . $orderId . '/assign-driver', ['driverId' => 'nonexistent-driver'], [200, 404, 422]);
$auth('POST', '/api/vendor/orders/' . $orderId . '/ship', [
    'courierCompanyName' => 'Test Courier',
    'courierTrackingId' => 'TRACK123',
], [200, 404, 422]);
$auth('PATCH', '/api/vendor/orders/' . $orderId, ['notes' => 'smoke test'], [200, 404, 422]);

// --- Products ---
$auth('GET', '/api/vendor/products');
$createProduct = request('POST', $baseUrl . '/api/vendor/products', $token, [
    'name' => 'Smoke Pizza ' . $ts,
    'description' => 'API test product',
    'price' => 299,
    'publish' => true,
    'veg' => true,
]);
record($results, 'POST /products', $createProduct, [200, 201, 422]);
$productId = $createProduct['json']['data']['id'] ?? null;
if ($productId) {
    $auth('GET', '/api/vendor/products/' . $productId);
    $auth('PUT', '/api/vendor/products/' . $productId, ['name' => 'Updated Pizza', 'price' => 349]);
    $auth('DELETE', '/api/vendor/products/' . $productId, null, [200, 204, 404]);
} else {
    $auth('GET', '/api/vendor/products/nonexistent-product', null, [404]);
}

// --- Coupons ---
$auth('GET', '/api/vendor/coupons');
$createCoupon = request('POST', $baseUrl . '/api/vendor/coupons', $token, [
    'code' => 'SMOKE' . substr((string) $ts, -6),
    'discount' => 10,
    'discountType' => 'Percentage',
    'expiresAt' => date('Y-m-d', strtotime('+30 days')),
]);
record($results, 'POST /coupons', $createCoupon, [200, 201, 422]);
$couponId = $createCoupon['json']['data']['id'] ?? null;
if ($couponId) {
    $auth('GET', '/api/vendor/coupons/' . $couponId);
    $auth('PUT', '/api/vendor/coupons/' . $couponId, ['discount' => 15]);
    $auth('DELETE', '/api/vendor/coupons/' . $couponId, null, [200, 204, 404]);
}

// --- Wallet ---
$auth('GET', '/api/vendor/wallet');
$auth('GET', '/api/vendor/wallet/transactions');
$auth('GET', '/api/vendor/earnings');
$auth('GET', '/api/vendor/wallet/payouts');
$auth('GET', '/api/vendor/withdraw-method');
$auth('PUT', '/api/vendor/withdraw-method', [
    'flutterWave' => ['accountNumber' => '123', 'bankCode' => '044'],
]);
$auth('POST', '/api/vendor/wallet/withdraw', [
    'amount' => 1,
    'withdrawMethod' => 'bank',
], [200, 201, 422]);

// --- Chat ---
$auth('GET', '/api/vendor/chat/inbox?type=customer');
$auth('GET', '/api/vendor/chat/inbox?type=admin');
$auth('GET', '/api/vendor/chat/' . $orderId . '/messages', null, [200, 404]);
$auth('POST', '/api/vendor/chat/send', [
    'orderId' => $orderId,
    'message' => 'Smoke test message',
    'chatType' => 'customer',
], [200, 201, 404, 422]);

// --- Reviews & ratings ---
$auth('GET', '/api/vendor/reviews');
$auth('GET', '/api/vendor/reviews/order/' . $orderId, null, [200, 404]);
$auth('GET', '/api/vendor/ratings');

// --- Drivers ---
$auth('GET', '/api/vendor/drivers');
$createDriver = request('POST', $baseUrl . '/api/vendor/drivers', $token, [
    'firstName' => 'Smoke',
    'lastName' => 'Driver',
    'email' => 'driver' . $ts . '@kweek.test',
    'phoneNumber' => '95' . substr((string) $ts, -8),
    'carNumber' => 'KA01XY9999',
]);
record($results, 'POST /drivers', $createDriver, [200, 201, 422]);
$driverId = $createDriver['json']['data']['driver']['id'] ?? $createDriver['json']['data']['id'] ?? null;
if ($driverId) {
    $auth('GET', '/api/vendor/drivers/' . $driverId);
    $auth('PUT', '/api/vendor/drivers/' . $driverId, ['carNumber' => 'KA01ZZ1234']);
}

// --- Dine-in ---
$auth('GET', '/api/vendor/dine-in/bookings');
$auth('GET', '/api/vendor/dine-in/bookings?tab=upcoming');
$auth('GET', '/api/vendor/dine-in/bookings?tab=past');
$auth('GET', '/api/vendor/dine-in/bookings/' . $bookingId, null, [200, 404]);
$auth('POST', '/api/vendor/dine-in/bookings/' . $bookingId . '/accept', null, [200, 404, 422]);
$auth('POST', '/api/vendor/dine-in/bookings/' . $bookingId . '/reject', ['reason' => 'Full'], [200, 404, 422]);
$auth('PUT', '/api/vendor/dine-in/config', [
    'dine_in_active' => true,
    'openDineTime' => '10:00',
    'closeDineTime' => '22:00',
]);

// --- Subscriptions ---
$auth('GET', '/api/vendor/subscriptions/history');
$auth('POST', '/api/vendor/subscriptions', [
    'plan_id' => 'nonexistent-plan',
    'payment_type' => 'manual',
], [200, 201, 404, 422]);

// --- Advertisements ---
$auth('GET', '/api/vendor/advertisements');
$createAd = request('POST', $baseUrl . '/api/vendor/advertisements', $token, [
    'title' => 'Smoke Ad ' . $ts,
    'description' => 'API test ad',
    'type' => 'restaurant_promotion',
]);
record($results, 'POST /advertisements', $createAd, [200, 201, 422]);
$advertisementId = $createAd['json']['data']['id'] ?? null;
if ($advertisementId) {
    $auth('GET', '/api/vendor/advertisements/' . $advertisementId);
    $auth('PUT', '/api/vendor/advertisements/' . $advertisementId, ['title' => 'Updated Ad']);
    $auth('DELETE', '/api/vendor/advertisements/' . $advertisementId, null, [200, 204, 404]);
}

// --- Story ---
$auth('GET', '/api/vendor/story');
$auth('POST', '/api/vendor/story', [
    'videoUrl' => [],
    'thumbnailUrl' => 'https://example.com/thumb.jpg',
], [200, 201, 422]);
$auth('DELETE', '/api/vendor/story', null, [200, 204, 404]);

// --- Documents & notifications ---
$auth('GET', '/api/vendor/documents');
$auth('GET', '/api/vendor/documents/status');
$auth('POST', '/api/vendor/documents', ['documents' => []], [200, 201, 422]);
$auth('GET', '/api/vendor/notifications');

// --- Logout (last) ---
$auth('POST', '/api/vendor/logout', null, [200, 204]);

// Report
$serverErrors = array_filter($results['fail'], fn ($f) => $f['code'] >= 500 || str_contains($f['message'] ?? '', 'exception'));

echo str_repeat('=', 60) . "\n";
echo 'PASSED: ' . count($results['pass']) . "\n";
echo 'FAILED: ' . count($results['fail']) . "\n";
echo 'SERVER ERRORS (5xx): ' . count($serverErrors) . "\n";
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

echo "=== ALL RESULTS ===\n";
foreach ($results['pass'] as $p) {
    echo "  OK  [{$p['code']}] {$p['name']}\n";
}
foreach ($results['fail'] as $f) {
    echo "  FAIL[{$f['code']}] {$f['name']}\n";
}

exit(count($serverErrors) > 0 ? 1 : 0);
