<?php
require 'e:/Nexa_Project/vendor/autoload.php';
$app = require 'e:/Nexa_Project/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\BannerController;
use Illuminate\Http\Request;

// Log in as user ID 1
$user = \App\Models\AppUser::find('1') ?? \App\Models\AppUser::first();
auth()->login($user);

$controller = new BannerController();

// 1. With sectionId '6285dd3281531'
$request = Request::create('/banners/datatable', 'GET', [
    'draw' => 1,
    'start' => 0,
    'length' => 10,
    'sectionId' => '6285dd3281531'
]);
$response = $controller->datatable($request);
$data1 = $response->getData();

// 2. Without sectionId
$requestNoSection = Request::create('/banners/datatable', 'GET', [
    'draw' => 1,
    'start' => 0,
    'length' => 10
]);
$responseNoSection = $controller->datatable($requestNoSection);
$data2 = $responseNoSection->getData();

$output = [
    'with_section' => $data1,
    'without_section' => $data2
];

file_put_contents('e:/Nexa_Project/scratch/datatable_output_fixed.json', json_encode($output, JSON_PRETTY_PRINT));
echo "Done!\n";
