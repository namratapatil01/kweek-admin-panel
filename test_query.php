<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$setting = \App\Models\Setting::find('globalSettings');
if ($setting) {
    echo json_encode($setting->value, JSON_PRETTY_PRINT);
} else {
    echo "Not found";
}
