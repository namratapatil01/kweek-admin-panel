<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$row = \DB::table('settings')->first();
foreach((array)$row as $k => $v) {
    if (is_string($v) && strpos($v, 'AIza') === 0) {
        echo "Found Google API key in column: $k => $v\n";
    }
}
