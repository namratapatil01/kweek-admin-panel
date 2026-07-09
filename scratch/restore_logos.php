<?php
require 'e:/Nexa_Project/vendor/autoload.php';
$app = require 'e:/Nexa_Project/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// The local storage URLs for KweeK logos
$baseUrl = config('app.url') . '/storage/images/';
$kweekLogo = $baseUrl . 'kweek-logo.png';
$kweekIcon = $baseUrl . 'kweek_icon.png';

// Fetch current globalSettings row
$s = \DB::table('settings')->where('id', 'globalSettings')->first();
$v = json_decode($s->value, true);

// Show current values
echo "=== BEFORE ===\n";
echo "app_logo: " . ($v['app_logo'] ?? 'null') . "\n";
echo "provider_logo: " . ($v['provider_logo'] ?? 'null') . "\n";
echo "worker_logo: " . ($v['worker_logo'] ?? 'null') . "\n";

// Update logos to local KweeK logo
$v['app_logo'] = $kweekLogo;
$v['provider_logo'] = $kweekLogo;
$v['worker_logo'] = $kweekLogo;
$v['appLogo'] = $kweekLogo;
$v['providerLogo'] = $kweekLogo;
$v['workerLogo'] = $kweekLogo;

\DB::table('settings')->where('id', 'globalSettings')->update(['value' => json_encode($v)]);

echo "\n=== AFTER ===\n";
echo "app_logo: " . $kweekLogo . "\n";
echo "provider_logo: " . $kweekLogo . "\n";
echo "worker_logo: " . $kweekLogo . "\n";
echo "\nDone! KweeK logos restored.\n";
