<?php
require 'e:/Nexa_Project/vendor/autoload.php';
$app = require 'e:/Nexa_Project/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== APP USERS WITH PAYLOAD OR SETTINGS ===\n";
$users = \DB::table('app_users')->get(['id', 'firstName', 'lastName', 'payload', 'settings']);
foreach ($users as $u) {
    if ($u->payload) {
        $p = json_decode($u->payload, true);
        if (isset($p['adminCommission']) || isset($p['commission'])) {
            echo "User ID: {$u->id} | Name: {$u->firstName} {$u->lastName} | Payload: {$u->payload}\n";
        }
    }
    if ($u->settings) {
        $s = json_decode($u->settings, true);
        if (isset($s['adminCommission']) || isset($s['commission'])) {
            echo "User ID: {$u->id} | Name: {$u->firstName} {$u->lastName} | Settings: {$u->settings}\n";
        }
    }
}

echo "\n=== VENDORS WITH PAYLOAD ===\n";
$vendors = \DB::table('vendors')->get(['id', 'title', 'payload', 'adminCommission']);
foreach ($vendors as $v) {
    if ($v->payload) {
        $p = json_decode($v->payload, true);
        if (isset($p['adminCommission']) || isset($p['commission'])) {
            echo "Vendor ID: {$v->id} | Title: {$v->title} | Payload: {$v->payload}\n";
        }
    }
    if ($v->adminCommission) {
        echo "Vendor ID: {$v->id} | Title: {$v->title} | adminCommission field: {$v->adminCommission}\n";
    }
}
