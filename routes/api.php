<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Proxy for ArroPay API v2 auth login.
// Example:
// POST /api/v2/auth/login
// body: {"apiSecret":"1234"}
// ArroPay apiKey/apiSecret are loaded from Firebase settings/arropay_auth_settings.
Route::post('v2/auth/login', [\App\Http\Controllers\ArroPayV2AuthApiController::class, 'login']);

// ArroPay bank disbursement API (INSTAPAY / PESONET).
Route::prefix('v1/disbursement')->group(function () {
    Route::post('banks', [\App\Http\Controllers\ArroPayDisbursementController::class, 'banks']);
    Route::post('initiatebankwithdraw', [\App\Http\Controllers\ArroPayDisbursementController::class, 'initiateBankWithdraw']);
    Route::post('processbankwithdraw', [\App\Http\Controllers\ArroPayDisbursementController::class, 'processBankWithdraw']);
});
