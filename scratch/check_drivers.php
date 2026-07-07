<?php

// Check driver creation and list issues
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// 1. Count drivers
$total = DB::table('app_users')->where('role', 'driver')->count();
echo "Total drivers in DB: $total\n";

// 2. Count non-fleet drivers (isOwner = 0 or false)
$nonFleet = DB::table('app_users')
    ->where('role', 'driver')
    ->where(function($q) {
        $q->whereNull('ownerId')->orWhere('ownerId', '');
    })
    ->count();
echo "Non-fleet drivers (no ownerId): $nonFleet\n";

// 3. isOwner values breakdown
$ownerTrue  = DB::table('app_users')->where('role', 'driver')->where('isOwner', 1)->count();
$ownerFalse = DB::table('app_users')->where('role', 'driver')->where('isOwner', 0)->count();
$ownerNull  = DB::table('app_users')->where('role', 'driver')->whereNull('isOwner')->count();
echo "isOwner = 1 (owner): $ownerTrue\n";
echo "isOwner = 0 (driver): $ownerFalse\n";
echo "isOwner = NULL: $ownerNull\n";

// 4. Check recently added drivers (last 5)
echo "\n--- Last 5 drivers added ---\n";
$recent = DB::table('app_users')
    ->where('role', 'driver')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get(['id', 'firstName', 'lastName', 'email', 'isOwner', 'ownerId', 'serviceType', 'created_at']);

foreach ($recent as $d) {
    echo "  {$d->firstName} {$d->lastName} | email: {$d->email} | isOwner: {$d->isOwner} | ownerId: {$d->ownerId} | service: {$d->serviceType} | created: {$d->created_at}\n";
}

// 5. Sample the TestDriver Two specifically
echo "\n--- TestDriver Two ---\n";
$test = DB::table('app_users')->where('email', 'testdriver2@kweek.com')->first();
if ($test) {
    echo "Found: {$test->firstName} {$test->lastName}\n";
    echo "  role: {$test->role}\n";
    echo "  isOwner: {$test->isOwner}\n";
    echo "  ownerId: {$test->ownerId}\n";
    echo "  active: {$test->active}\n";
} else {
    echo "NOT FOUND in DB!\n";
}
