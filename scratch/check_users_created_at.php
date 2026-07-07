<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$record = DB::table('app_users')->whereNotNull('createdAt')->first();
if ($record) {
    echo "Found user with createdAt column value: " . $record->createdAt . "\n";
    echo "Payload contents (slice): " . json_encode(array_slice(json_decode($record->payload ?? '{}', true), 0, 5), JSON_PRETTY_PRINT) . "\n";
} else {
    echo "No user found with non-null createdAt column.\n";
    $anyUser = DB::table('app_users')->first();
    if ($anyUser) {
        echo "Any user ID: " . $anyUser->id . "\n";
        echo "created_at column: " . $anyUser->created_at . "\n";
        echo "createdAt column: " . ($anyUser->createdAt ?? 'N/A') . "\n";
        echo "Payload: " . json_encode(array_slice(json_decode($anyUser->payload ?? '{}', true), 0, 5), JSON_PRETTY_PRINT) . "\n";
    }
}
