<?php
require 'e:/Nexa_Project/vendor/autoload.php';
$app = require 'e:/Nexa_Project/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AppUser;
use App\Support\AdminPermissionResolver;

$users = \App\Models\User::all();
foreach ($users as $user) {
    $routes = AdminPermissionResolver::routesForUser($user);
    $hasBannersDelete = in_array('banners.delete', $routes);
    echo "User: {$user->email} | Role ID: {$user->role_id} | Banners.delete: " . ($hasBannersDelete ? 'YES' : 'NO') . "\n";
}
