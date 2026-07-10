<?php
require 'e:/Nexa_Project/vendor/autoload.php';
$app = require 'e:/Nexa_Project/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== VENDORS WITH COMMISSION ===\n";
$vendors = \DB::table('vendors')
    ->whereNotNull('adminCommission')
    ->get(['id', 'title', 'adminCommission']);
foreach ($vendors as $v) {
    echo "Vendor ID: {$v->id} | Title: {$v->title} | Commission: {$v->adminCommission}\n";
}

echo "\n=== APP USERS WITH COMMISSION ===\n";
$users = \DB::table('app_users')
    ->whereNotNull('adminCommission')
    ->get(['id', 'firstName', 'lastName', 'adminCommission']);
foreach ($users as $u) {
    echo "User ID: {$u->id} | Name: {$u->firstName} {$u->lastName} | Commission: {$u->adminCommission}\n";
}
