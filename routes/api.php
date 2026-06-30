<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EntityApiController;
use App\Http\Controllers\Api\V1\FileUploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| KWEEK REST API
|--------------------------------------------------------------------------
|
| Mobile apps use these endpoints for auth, entities, and file uploads.
|
*/

Route::prefix('v1')->group(function () {
  Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('uploads', [FileUploadController::class, 'store']);
        Route::delete('uploads', [FileUploadController::class, 'destroy']);

        Route::get('entities', function () {
            return response()->json([
                'success' => true,
                'data' => app(\App\Services\EntityRegistry::class)->slugs(),
            ]);
        });

        Route::get('{entity}', [EntityApiController::class, 'index'])
            ->where('entity', '[a-z0-9\-]+');
        Route::get('{entity}/{id}', [EntityApiController::class, 'show'])
            ->where('entity', '[a-z0-9\-]+');
        Route::post('{entity}', [EntityApiController::class, 'store'])
            ->where('entity', '[a-z0-9\-]+');
        Route::match(['put', 'patch'], '{entity}/{id}', [EntityApiController::class, 'update'])
            ->where('entity', '[a-z0-9\-]+');
        Route::delete('{entity}/{id}', [EntityApiController::class, 'destroy'])
            ->where('entity', '[a-z0-9\-]+');
    });

    Route::middleware(['auth:sanctum', 'app.role:admin'])->group(function () {
        // Reserved for admin-only mutations when mobile clients are read-heavy.
    });
});

// Legacy ArroPay routes (payment gateway — not Firestore CRUD)
Route::post('v2/auth/login', [\App\Http\Controllers\ArroPayV2AuthApiController::class, 'login']);

Route::prefix('v1/disbursement')->group(function () {
    Route::post('banks', [\App\Http\Controllers\ArroPayDisbursementController::class, 'banks']);
    Route::post('initiatebankwithdraw', [\App\Http\Controllers\ArroPayDisbursementController::class, 'initiateBankWithdraw']);
    Route::post('processbankwithdraw', [\App\Http\Controllers\ArroPayDisbursementController::class, 'processBankWithdraw']);
});
