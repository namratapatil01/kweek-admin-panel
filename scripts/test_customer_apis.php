<?php

/**
 * Smoke-test all /api/customer endpoints — flags 5xx and unexpected exceptions.
 * Run: php scripts/test_customer_apis.php
 */

$baseUrl = getenv('API_BASE_URL') ?: 'http://127.0.0.1:8000';
$sectionId = '6285dcf511651';
$vendorId = '0btqvc3WonrJH9abzWCq';
$productId = '0f170ff8-2a53-4fcf-a80f-6a7b412efe16';
$orderId = '00442e7a-e92e-488d-8afa-65b08606c768';
$notificationId = 'jdQ4asGWt79NPdHcset1';
$giftId = 'AekwluMXVu418bZjgvPD';
$serviceId = '0PFY5JSpFBXGzwlElN5j';
$categoryId = '62ecef57887cb';
$driverId = 'test-driver-id';

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

    usleep(100000); // 100ms between requests to avoid rate limiting

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
        // 401/422 etc. = expected for some auth/validation endpoints without real tokens
        $results['pass'][] = $entry;
    } else {
        $results['fail'][] = $entry;
    }
}

echo "=== KWEEK Customer API Smoke Test ===\n";
echo "Base URL: {$baseUrl}\n\n";

// Health check
$health = request('GET', $baseUrl . '/api/customer/home');
if ($health['curl_error'] || $health['code'] === 0) {
    echo "ERROR: Server not reachable at {$baseUrl}\n";
    echo "Start with: php artisan serve --host=127.0.0.1 --port=8000\n";
    exit(1);
}

// Login to get token
$ts = time();
$login = request('POST', $baseUrl . '/api/customer/login', null, [
    'email' => 'apitest' . $ts . '@kweek.test',
    'password' => 'password123',
]);

$token = $login['json']['token'] ?? null;

if (! $token) {
    $register = request('POST', $baseUrl . '/api/customer/register', null, [
        'firstName' => 'API',
        'lastName' => 'Test',
        'email' => 'apitest' . $ts . '@kweek.test',
        'phoneNumber' => '99' . substr((string) $ts, -8),
        'countryCode' => '+91',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);
    $token = $register['json']['token'] ?? null;
}

if (! $token) {
    echo "WARN: Could not register/login — using existing customer token from DB\n";
    // fallback: create token via artisan in shell — use phone login with auto_register
    $phone = request('POST', $baseUrl . '/api/customer/auth/phone', null, [
        'phoneNumber' => '88' . substr((string) $ts, -8),
        'countryCode' => '+91',
        'auto_register' => true,
    ]);
    $token = $phone['json']['token'] ?? null;
}

if (! $token) {
    echo "ERROR: Could not obtain auth token\n";
    exit(1);
}

echo "Auth token obtained.\n\n";

$pub = function (string $m, string $path, ?array $body = null, array $codes = [200, 201]) use (&$results, $baseUrl) {
    record(
        $results,
        "{$m} {$path}",
        request($m, $baseUrl . $path, null, $body),
        $codes
    );
};

$auth = function (string $m, string $path, ?array $body = null, array $codes = [200, 201]) use (&$results, $baseUrl, &$token) {
    record(
        $results,
        "{$m} {$path}",
        request($m, $baseUrl . $path, $token, $body),
        $codes
    );
};

// --- Public endpoints ---
$pub('GET', '/api/customer/home');
$pub('GET', '/api/customer/referral/validate?code=REFTEST123');
$pub('POST', '/api/customer/password/forgot', ['email' => 'jinal@gmail.com'], [200, 422]);

// Auth endpoints (expected 401/422 without real OAuth tokens)
$pub('POST', '/api/customer/auth/google', ['id_token' => 'invalid'], [401, 422]);
$pub('POST', '/api/customer/auth/apple', ['id_token' => 'invalid'], [401, 422]);

// --- Authenticated GET endpoints ---
$auth('GET', '/api/customer/profile');
$auth('GET', '/api/customer/dashboard');
$auth('GET', '/api/customer/settings');
$auth('GET', '/api/customer/settings/payment');
$auth('GET', '/api/customer/settings/languages');
$auth('GET', '/api/customer/settings/delivery-charge');
$auth('GET', '/api/customer/settings/globalSettings');

$auth('GET', '/api/customer/sections');
$auth('GET', '/api/customer/categories?section_id=' . $sectionId);
$auth('GET', '/api/customer/vendors?section_id=' . $sectionId);
$auth('GET', '/api/customer/vendors/nearest?section_id=' . $sectionId . '&latitude=23.03&longitude=72.58');
$auth('GET', '/api/customer/products?section_id=' . $sectionId);
$auth('GET', '/api/customer/services?section_id=' . $sectionId);
$auth('GET', '/api/customer/brands?section_id=' . $sectionId);
$auth('GET', '/api/customer/search?q=test&section_id=' . $sectionId);
$auth('GET', '/api/customer/advertisements?section_id=' . $sectionId);
$auth('GET', '/api/customer/banners?section_id=' . $sectionId);
$auth('GET', '/api/customer/stories?section_id=' . $sectionId);
$auth('GET', '/api/customer/zones?section_id=' . $sectionId);
$auth('GET', '/api/customer/taxes?section_id=' . $sectionId);
$auth('GET', '/api/customer/parcel/categories?section_id=' . $sectionId);
$auth('GET', '/api/customer/parcel/weights');
$auth('GET', '/api/customer/cab/vehicle-types?section_id=' . $sectionId);
$auth('GET', '/api/customer/cab/popular-destinations?section_id=' . $sectionId);
$auth('GET', '/api/customer/rental/vehicle-types?section_id=' . $sectionId);
$auth('GET', '/api/customer/rental/packages?section_id=' . $sectionId);
$auth('GET', '/api/customer/provider/workers?section_id=' . $sectionId);
$auth('GET', '/api/customer/review-attributes');
$auth('GET', '/api/customer/vendor-attributes');
$auth('GET', '/api/customer/vendors/' . $vendorId . '/cuisines');
$auth('GET', '/api/customer/catalog/vendor/' . $vendorId);
$auth('GET', '/api/customer/catalog/product/' . $productId);
$auth('GET', '/api/customer/catalog/service/' . $serviceId);
$auth('GET', '/api/customer/catalog/category/' . $categoryId);

$auth('GET', '/api/customer/orders');
$auth('GET', '/api/customer/orders/vendor/' . $orderId);
$auth('GET', '/api/customer/tracking/orders/vendor/' . $orderId);
$auth('GET', '/api/customer/tracking/drivers/' . $driverId, null, [200, 404]);

$auth('GET', '/api/customer/favorites/vendor');
$auth('GET', '/api/customer/favorites/item');
$auth('GET', '/api/customer/favorites/service');
$auth('GET', '/api/customer/favorites/provider');

$auth('GET', '/api/customer/wallet');
$auth('GET', '/api/customer/wallet/transactions');

$auth('GET', '/api/customer/reviews');
$auth('GET', '/api/customer/reviews/vendor/' . $vendorId);
$auth('GET', '/api/customer/reviews/service/' . $serviceId);

$auth('GET', '/api/customer/coupons?section_id=' . $sectionId);

$auth('GET', '/api/customer/cashback');
$auth('GET', '/api/customer/cashback/redeemed');

$auth('GET', '/api/customer/chat/store/inbox');
$auth('GET', '/api/customer/chat/store/' . $orderId . '/messages', null, [200, 404]);

$auth('GET', '/api/customer/notifications');
$auth('GET', '/api/customer/notifications/content/order_placed');
$auth('PATCH', '/api/customer/notifications/' . $notificationId . '/read');

$auth('GET', '/api/customer/referral');
$auth('GET', '/api/customer/gift-cards');
$auth('GET', '/api/customer/gift-cards/history');
$auth('GET', '/api/customer/email-templates/forgot_password', null, [200, 404]);
$auth('GET', '/api/customer/complaints?orderId=' . $orderId);

// --- Authenticated POST/PATCH endpoints (minimal payloads) ---
$auth('PUT', '/api/customer/profile', ['firstName' => 'API', 'lastName' => 'Test']);
$auth('POST', '/api/customer/referral', ['referralCode' => 'SMOKE' . $ts, 'referralBy' => '', 'isSuccessful' => false], [200, 201]);
$auth('POST', '/api/customer/referral/rewards');
$auth('POST', '/api/customer/wallet/topup', ['amount' => 1, 'payment_method' => 'test'], [200, 201, 422]);
$auth('POST', '/api/customer/cashback/redeem', ['cashback_id' => 'nonexistent'], [200, 404, 422]);
$auth('POST', '/api/customer/favorites/vendor', ['store_id' => $vendorId, 'section_id' => $sectionId], [200, 201, 422]);
$auth('POST', '/api/customer/chat/store/send', ['orderId' => $orderId, 'message' => 'test'], [200, 201, 422]);
$auth('POST', '/api/customer/gift-cards/purchase', ['giftId' => $giftId, 'price' => 10], [200, 201, 422]);
$auth('POST', '/api/customer/gift-cards/redeem', ['giftCode' => 'INVALID'], [200, 422]);
$auth('POST', '/api/customer/complaints', [
    'orderId' => 'smoke-complaint-' . $ts,
    'title' => 'Smoke test',
    'description' => 'API smoke test complaint',
], [200, 201, 409]);
$auth('POST', '/api/customer/sos', ['orderId' => $orderId, 'status' => 'Initiated'], [200, 201, 422]);
$auth('POST', '/api/customer/reviews', [
    'orderid' => $orderId,
    'VendorId' => $vendorId,
    'productId' => $productId,
    'rating' => 5,
    'comment' => 'smoke test',
], [200, 201, 422]);
$auth('POST', '/api/customer/ratings', ['orderid' => $orderId, 'rating' => 5], [200, 201, 422]);

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

echo "=== ALL RESULTS ===\n";
foreach ($results['pass'] as $p) {
    echo "  OK  [{$p['code']}] {$p['name']}\n";
}
foreach ($results['fail'] as $f) {
    echo "  FAIL[{$f['code']}] {$f['name']}\n";
}

exit(count($serverErrors) > 0 ? 1 : 0);
