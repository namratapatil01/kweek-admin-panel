<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$row = \DB::table('settings')->first();
echo "selected_map_type: " . ($row->selected_map_type ?? 'not set') . "\n";
echo "map_type: " . ($row->map_type ?? 'not set') . "\n";
