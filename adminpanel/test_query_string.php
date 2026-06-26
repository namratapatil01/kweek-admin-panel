<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/api/firestore/query', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
    'collection' => 'currencies',
    'wheres' => [['isActive', '==', 'true']]
]));

$response = app('App\Http\Controllers\FirestoreBridgeController')->query($request);
echo $response->getContent();
