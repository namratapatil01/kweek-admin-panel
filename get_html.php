<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'admin@emart.com')->first();
auth()->login($user);

$request = Illuminate\Http\Request::create('/dashboard', 'GET');
$response = app()->handle($request);
file_put_contents('dashboard_output.html', $response->getContent());
echo "HTML written to dashboard_output.html";
