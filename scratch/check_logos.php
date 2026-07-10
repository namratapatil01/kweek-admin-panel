<?php
require 'e:/Nexa_Project/vendor/autoload.php';
$app = require 'e:/Nexa_Project/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$s = \DB::table('settings')->where('id','globalSettings')->first();
$v = json_decode($s->value, true);

echo "app_logo: " . ($v['app_logo'] ?? 'null') . "\n";
echo "provider_logo: " . ($v['provider_logo'] ?? 'null') . "\n";
echo "worker_logo: " . ($v['worker_logo'] ?? 'null') . "\n";
echo "home_banner: " . ($v['home_banner'] ?? 'null') . "\n";
