<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$controller = app(App\Http\Controllers\MapController::class);

$food = json_decode($controller->getFoodData()->getContent(), true);
$targets = ['niv', 'muba jaha', 'test hh', 'd vc'];
foreach ($food['drivers'] as $driver) {
    $full = trim(($driver['firstName'] ?? '') . ' ' . ($driver['lastName'] ?? ''));
    foreach ($targets as $t) {
        if (stripos($full, str_replace(' driver', '', $t)) !== false || stripos($full, $t) !== false) {
            echo $full . ' => lat=' . ($driver['latitude'] ?? 'null') . ' lng=' . ($driver['longitude'] ?? 'null') . PHP_EOL;
        }
    }
}

$req = Illuminate\Http\Request::create('/', 'GET', ['section_id' => '6285ddbfd9598']);
$legacy = json_decode($controller->getMultivendorData($req)->getContent(), true);
echo "Legacy section drivers: " . count($legacy['drivers']) . PHP_EOL;

$req2 = Illuminate\Http\Request::create('/', 'GET', ['section_id' => '1']);
$cosmetic = json_decode($controller->getMultivendorData($req2)->getContent(), true);
echo "Cosmetic (section 1) drivers: " . count($cosmetic['drivers']) . PHP_EOL;

foreach (['muba', 'test hh', 'd vc', 'niv'] as $name) {
    $rows = DB::table('app_users')
        ->where('role', 'driver')
        ->where(function ($q) use ($name) {
            $q->where('firstName', 'like', '%' . $name . '%')
                ->orWhere('lastName', 'like', '%' . $name . '%');
        })
        ->get(['firstName', 'lastName', 'sectionId', 'section_id']);
    echo $name . ': ' . json_encode($rows) . PHP_EOL;
}

$req3 = Illuminate\Http\Request::create('/', 'GET', ['section_id' => '3']);
$foodStrict = json_decode($controller->getMultivendorData($req3)->getContent(), true);

// Count drivers assigned to section 3 only (no unassigned)
$strictCount = DB::table('app_users')
    ->where('role', 'driver')
    ->where('serviceType', 'delivery-service')
    ->where(function ($q) {
        $q->where('sectionId', '3')
            ->orWhere('section_id', '3')
            ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.sectionId')) = '3'");
    })
    ->count();
echo "Strict section 3 drivers in DB: $strictCount" . PHP_EOL;

// Drivers with valid coords for screenshot names
$names = ['niv driver', 'muba jaha', 'test hh', 'd vc'];
foreach ($names as $name) {
    $parts = explode(' ', $name, 2);
    $row = DB::table('app_users')
        ->where('role', 'driver')
        ->where('serviceType', 'delivery-service')
        ->where('firstName', 'like', '%' . trim($parts[0]) . '%')
        ->when(isset($parts[1]), fn ($q) => $q->where('lastName', 'like', '%' . trim($parts[1]) . '%'))
        ->first(['firstName', 'lastName', 'latitude', 'longitude', 'sectionId']);
    echo $name . ': ' . json_encode($row) . PHP_EOL;
}
