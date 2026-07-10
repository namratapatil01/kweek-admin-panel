<?php
require 'e:/Nexa_Project/vendor/autoload.php';
$app = require 'e:/Nexa_Project/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$items = \DB::table('banner_items')->get();
foreach ($items as $item) {
    echo "ID: {$item->id} | Title: {$item->title} | Payload: {$item->payload}\n";
}
