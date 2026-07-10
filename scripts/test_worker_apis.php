<?php

/**
 * Smoke-test all /api/worker endpoints.
 * Run:
 *   php scripts/sync_worker_users.php
 *   php scripts/test_worker_apis.php
 */

$baseUrl = getenv('API_BASE_URL') ?: 'http://127.0.0.1:8000';
$email = getenv('WORKER_TEST_EMAIL') ?: 'tpenvarne3@godaddy.com';
$password = getenv('WORKER_TEST_PASSWORD') ?: 'password123';

$results = ['pass' => [], 'fail' => [], 'skip' => []];
$token = null;
$jobId = null;
$providerId = null;

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

function extractJobId(array $res): ?string
{
    $data = $res['json']['data'] ?? null;
    if (! is_array($data)) {
        return null;
    }
    if (isset($data[0]['id'])) {
        return $data[0]['id'];
    }
    if (isset($data['data'][0]['id'])) {
        return $data['data'][0]['id'];
    }

    return $data['id'] ?? null;
}

echo "=== KWEEK Worker API Smoke Test ===\n";
echo "Base URL: {$baseUrl}\n";
echo "Email: {$email}\n\n";

$health = request('GET', $baseUrl . '/api/worker/home');
if ($health['curl_error'] || $health['code'] === 0) {
    echo "ERROR: Server not reachable at {$baseUrl}\n";
    exit(1);
}

// Public endpoints
record($results, 'GET /home', $health);
record($results, 'GET /settings', request('GET', "{$baseUrl}/api/worker/settings"));
record($results, 'GET /settings/languages', request('GET', "{$baseUrl}/api/worker/settings/languages"));
record($results, 'GET /settings/Version', request('GET', "{$baseUrl}/api/worker/settings/Version"));
record($results, 'GET /settings/googleMapKey', request('GET', "{$baseUrl}/api/worker/settings/googleMapKey"));
record($results, 'GET /settings/placeHolderImage', request('GET', "{$baseUrl}/api/worker/settings/placeHolderImage"));
record($results, 'GET /settings/notification_setting', request('GET', "{$baseUrl}/api/worker/settings/notification_setting"));
record($results, 'GET /terms', request('GET', "{$baseUrl}/api/worker/terms"));
record($results, 'GET /privacy', request('GET', "{$baseUrl}/api/worker/privacy"));
record($results, 'POST /password/forgot', request('POST', "{$baseUrl}/api/worker/password/forgot", null, ['email' => $email]));
record($results, 'POST /password/reset (invalid token)', request('POST', "{$baseUrl}/api/worker/password/reset", null, [
    'email' => $email,
    'token' => 'invalid-token',
    'password' => 'newpass123',
    'password_confirmation' => 'newpass123',
]), [422]);

$login = request('POST', "{$baseUrl}/api/worker/login", null, [
    'email' => $email,
    'password' => $password,
    'fcmToken' => 'test-worker-fcm',
]);
record($results, 'POST /login', $login, [200, 401, 422]);
$token = $login['json']['token'] ?? null;

if (! $token) {
    echo "\nNo auth token. Run: php scripts/sync_worker_users.php\n";
    echo "Login response: " . substr((string) $login['body'], 0, 300) . "\n\n";
} else {
    $auth = function (string $method, string $path, ?array $body = null, array $codes = [200, 201]) use (&$results, $baseUrl, $token): array {
        $res = request($method, "{$baseUrl}/api/worker{$path}", $token, $body);
        record($results, "{$method} {$path}", $res, $codes);

        return $res;
    };

    $profile = $auth('GET', '/profile');
    $providerId = $profile['json']['data']['providerId'] ?? $profile['json']['worker']['providerId'] ?? null;

    $auth('PUT', '/profile', ['phoneNumber' => '9999999999']);
    $auth('PUT', '/availability', ['online' => true]);
    $auth('GET', '/provider');
    $auth('GET', '/dashboard');
    $auth('GET', '/realtime/poll?since=' . urlencode(date('c', time() - 300)));
    $auth('GET', '/realtime/poll?tab=upcoming&since=' . urlencode(date('c', time() - 3600)));

    $jobsUpcoming = $auth('GET', '/jobs?tab=upcoming');
    $auth('GET', '/jobs?tab=today');
    $auth('GET', '/jobs?tab=completed');
    $auth('GET', '/jobs?tab=ongoing');
    $auth('GET', '/jobs?tab=cancelled');

    $jobId = extractJobId($jobsUpcoming);

    if ($jobId) {
        $auth('GET', "/jobs/{$jobId}");
        $auth('POST', "/jobs/{$jobId}/accept", null, [200, 422]);
        $auth('POST', "/jobs/{$jobId}/reject", ['reason' => 'Smoke test'], [200, 422]);
        $auth('POST', "/jobs/{$jobId}/start", null, [200, 422]);
        $auth('POST', "/jobs/{$jobId}/stop-timer", null, [200, 422]);
        $auth('POST', "/jobs/{$jobId}/extra-charges", [
            'extraCharges' => 50,
            'extraChargesDescription' => 'Smoke test charge',
        ], [200, 422]);
        $auth('POST', "/jobs/{$jobId}/complete", ['otp' => '000000'], [200, 422]);
        $auth('PATCH', "/jobs/{$jobId}/status", ['status' => 'Order Ongoing'], [200, 422]);
    } else {
        $results['skip'][] = ['name' => 'Job detail/actions', 'code' => 0, 'message' => 'No job id for worker'];
        $auth('GET', '/jobs/nonexistent-job-id', null, [404]);
    }

    $auth('GET', '/chat/inbox');
    if ($jobId) {
        $auth('GET', "/chat/{$jobId}/messages");
        $auth('POST', '/chat/send', [
            'orderId' => $jobId,
            'message' => 'API smoke test message',
            'messageType' => 'text',
        ], [200, 201, 422]);
    }

    $auth('GET', '/reviews');
    if ($jobId) {
        $auth('GET', "/reviews/order/{$jobId}", null, [200, 404]);
    }
    $auth('GET', '/ratings');
    $auth('GET', '/earnings');
    $auth('GET', '/notifications');
    $auth('GET', '/notifications/templates/provider_service_intransit');
    $auth('GET', '/notifications/templates/worker_rejected');
    $auth('GET', '/documents');
    $auth('GET', '/documents/status');
    $auth('POST', '/documents', ['documents' => []], [200, 201, 422]);

    $auth('POST', '/logout', null, [200, 204]);
}

// Register smoke (optional — needs valid providerId)
if ($providerId) {
    $ts = time();
    $regEmail = "workerapitest{$ts}@kweek.test";
    record($results, 'POST /register', request('POST', "{$baseUrl}/api/worker/register", null, [
        'firstName' => 'API',
        'lastName' => 'Worker',
        'email' => $regEmail,
        'phoneNumber' => '98' . substr((string) $ts, -8),
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'providerId' => $providerId,
        'address' => 'Test address',
        'salary' => '15000',
    ]), [200, 201, 422]);
}

$serverErrors = array_filter($results['fail'], fn ($f) => $f['code'] >= 500);

echo "\n============================================================\n";
echo 'PASSED: ' . count($results['pass']) . "\n";
echo 'SKIP: ' . count($results['skip']) . "\n";
echo 'FAILED: ' . count($results['fail']) . "\n";
echo 'SERVER ERRORS (5xx): ' . count($serverErrors) . "\n";
echo "============================================================\n\n";

foreach ($results['pass'] as $p) {
    echo "  OK  [{$p['code']}] {$p['name']}\n";
}

if ($results['skip']) {
    echo "\nSkipped:\n";
    foreach ($results['skip'] as $s) {
        echo "  --  {$s['name']}: {$s['message']}\n";
    }
}

if ($results['fail']) {
    echo "\nFailures:\n";
    foreach ($results['fail'] as $f) {
        echo "  FAIL [{$f['code']}] {$f['name']}: {$f['message']}\n";
    }
    exit(1);
}

exit(0);
