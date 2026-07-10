<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$row = \DB::table('settings')->first();
$map_key = null;
foreach((array)$row as $key => $val) {
    if (stripos($key, 'map') !== false) {
        echo "Found map related key: $key => $val\n";
    }
}
