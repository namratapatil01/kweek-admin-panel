<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Fix: update all drivers with empty-string ownerId to NULL
$affected = DB::statement("UPDATE app_users SET ownerId = NULL WHERE role = 'driver' AND ownerId = ''");
echo "Statement result: " . ($affected ? 'true' : 'false') . "\n";

// Verify
$emptyOwner = DB::table('app_users')->where('role','driver')->where('ownerId','')->count();
$nullOwner  = DB::table('app_users')->where('role','driver')->whereNull('ownerId')->count();
echo "After fix — empty string ownerId: $emptyOwner\n";
echo "After fix — NULL ownerId: $nullOwner\n";

$test = DB::table('app_users')->where('email','testdriver2@kweek.com')->first(['firstName','ownerId','active']);
if ($test) {
    echo "TestDriver Two ownerId: " . var_export($test->ownerId, true) . "\n";
}
