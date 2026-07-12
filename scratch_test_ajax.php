<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate session for permissions
session(['user_permissions' => json_encode([
    'vendors.delete',
    'approve.vendors.delete',
    'pending.vendors.delete'
])]);

$request = Illuminate\Http\Request::create('/vendors/datatable', 'GET', [
    'type' => 'all',
    'draw' => 1,
    'start' => 0,
    'length' => 5
]);

$controller = $app->make(\App\Http\Controllers\VendorController::class);
$response = $controller->datatable($request);

echo "Status Code: " . $response->getStatusCode() . "\n";
$data = json_decode($response->getContent(), true);

if (isset($data['data'])) {
    foreach ($data['data'] as $index => $row) {
        echo "\nRow $index:\n";
        foreach ($row as $colIdx => $val) {
            echo "  Col $colIdx: " . strip_tags($val) . "\n";
        }
    }
} else {
    print_r($data);
}
