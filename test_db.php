<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $ads = DB::table('advertisements')->get();
    echo "Total Ads: " . count($ads) . "\n";
    foreach($ads as $ad) {
        echo "ID: {$ad->id}, Vendor: {$ad->vendorId}, Title: {$ad->title}\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
