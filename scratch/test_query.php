<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\DocumentStoreService;

$store = app(DocumentStoreService::class);

$sectionId = 'yJTddzJUxP3cOU5DpJ10'; // Home Services
$statuses = ["Order Placed", "Order Accepted", "Order Assigned", "Order Ongoing", "Order Completed", "Order Cancelled"];

echo "=== QUERY FOR SECTION: {$sectionId} ===\n";
$filters = [
    ['field' => 'sectionId', 'op' => '==', 'value' => $sectionId],
    ['field' => 'status', 'op' => 'IN', 'value' => $statuses]
];

$results = $store->queryForBridge('provider_orders', $filters, 10, 'createdAt', 'desc');
echo "Query returned " . count($results) . " records.\n";
foreach ($results as $index => $r) {
    echo "Record #{$index}: ID={$r['id']} | status={$r['status']} | sectionId=" . ($r['sectionId'] ?? 'N/A') . " | createdAt=" . ($r['createdAt'] ?? 'N/A') . "\n";
}
