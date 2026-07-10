<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$histories = DB::table('subscription_histories')->take(20)->get();
foreach ($histories as $h) {
    $p = json_decode($h->payload, true) ?: [];
    echo "ID: {$h->id}\n";
    echo "  expiry_date: " . json_encode($p['expiry_date'] ?? null) . "\n";
    echo "  expire_date: " . json_encode($p['expire_date'] ?? null) . "\n";
    echo "  data_created_at: " . json_encode($p['data_created_at'] ?? null) . "\n";
    echo "  createdAt: " . json_encode($p['createdAt'] ?? null) . "\n";
}
