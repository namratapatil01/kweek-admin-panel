<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = \Illuminate\Support\Facades\Schema::getColumnType('sections', 'isActive');
echo "isActive type: " . $columns . "\n";
$first = \Illuminate\Support\Facades\DB::table('sections')->first();
echo "first row: " . json_encode($first, JSON_PRETTY_PRINT) . "\n";

