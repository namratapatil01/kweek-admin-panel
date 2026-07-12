<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$attributes = [
    'Cleanliness',
    'Communication',
    'Timeliness',
    'Quality of Service',
    'Value for Money',
];

echo "Checking review_attributes table...\n";

foreach ($attributes as $attr) {
    $exists = DB::table('review_attributes')->where('title', $attr)->exists();
    if (!$exists) {
        DB::table('review_attributes')->insert([
            'id' => Str::uuid()->toString(),
            'title' => $attr,
            'isActive' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "Inserted attribute: {$attr}\n";
    } else {
        echo "Attribute already exists: {$attr}\n";
    }
}

echo "Done!\n";
