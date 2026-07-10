<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$vendors = DB::table('app_users')->where('role', 'vendor')->take(5)->get();
foreach ($vendors as $v) {
    echo "ID: " . $v->id . " | Name: " . $v->firstName . " " . $v->lastName . "\n";
    echo "Email: " . $v->email . " | Phone: " . $v->phoneNumber . "\n";
    echo "Created At: " . $v->created_at . "\n";
    echo "Active: " . $v->active . "\n";
    echo "isDocumentVerify: " . $v->isDocumentVerify . "\n";
    echo "----------------------------------------\n";
}
