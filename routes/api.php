<?php

use App\Http\Controllers\Api\Customer\CustomerAuthController;
use App\Http\Controllers\Api\Customer\CustomerCatalogController;
use App\Http\Controllers\Api\Customer\CustomerCouponController;
use App\Http\Controllers\Api\Customer\CustomerDashboardController;
use App\Http\Controllers\Api\Customer\CustomerFavoriteController;
use App\Http\Controllers\Api\Customer\CustomerMiscController;
use App\Http\Controllers\Api\Customer\CustomerOrderController;
use App\Http\Controllers\Api\Customer\CustomerProfileController;
use App\Http\Controllers\Api\Customer\CustomerReviewController;
use App\Http\Controllers\Api\Customer\CustomerWalletController;
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

Route::prefix('customer')->group(function () {
    Route::post('register', [CustomerAuthController::class, 'register']);
    Route::post('login', [CustomerAuthController::class, 'login']);
    Route::post('auth/google', [CustomerAuthController::class, 'loginWithGoogle']);
    Route::post('auth/apple', [CustomerAuthController::class, 'loginWithApple']);
    Route::post('password/forgot', [CustomerAuthController::class, 'forgotPassword']);
    Route::post('password/reset', [CustomerAuthController::class, 'resetPassword']);
    Route::get('home', [CustomerDashboardController::class, 'home']);

    Route::middleware(['auth:sanctum', 'app.role:customer'])->group(function () {
        Route::post('logout', [CustomerAuthController::class, 'logout']);

        Route::get('profile', [CustomerProfileController::class, 'show']);
        Route::put('profile', [CustomerProfileController::class, 'update']);
        Route::post('profile/image', [CustomerProfileController::class, 'uploadImage']);

        Route::get('dashboard', [CustomerDashboardController::class, 'dashboard']);

        Route::get('sections', [CustomerCatalogController::class, 'sections']);
        Route::get('categories', [CustomerCatalogController::class, 'categories']);
        Route::get('vendors', [CustomerCatalogController::class, 'vendors']);
        Route::get('products', [CustomerCatalogController::class, 'products']);
        Route::get('services', [CustomerCatalogController::class, 'services']);
        Route::get('brands', [CustomerCatalogController::class, 'brands']);
        Route::get('search', [CustomerCatalogController::class, 'search']);
        Route::get('catalog/{type}/{id}', [CustomerCatalogController::class, 'show'])
            ->where('type', 'vendor|product|service|category|provider-category|brand')
            ->where('id', '[a-zA-Z0-9\-_]+');

        Route::get('orders', [CustomerOrderController::class, 'index']);
        Route::post('orders', [CustomerOrderController::class, 'store']);
        Route::get('orders/{type}/{id}', [CustomerOrderController::class, 'show'])
            ->where('type', 'vendor|parcel|rental|ride|provider|dine-in');
        Route::patch('orders/{type}/{id}/status', [CustomerOrderController::class, 'updateStatus'])
            ->where('type', 'vendor|parcel|rental|ride|provider|dine-in');

        Route::get('favorites/{type}', [CustomerFavoriteController::class, 'index'])
            ->where('type', 'vendor|item|service|provider');
        Route::post('favorites/{type}', [CustomerFavoriteController::class, 'store'])
            ->where('type', 'vendor|item|service|provider');
        Route::delete('favorites/{type}/{id}', [CustomerFavoriteController::class, 'destroy'])
            ->where('type', 'vendor|item|service|provider');

        Route::get('wallet', [CustomerWalletController::class, 'balance']);
        Route::get('wallet/transactions', [CustomerWalletController::class, 'transactions']);
        Route::post('wallet/topup', [CustomerWalletController::class, 'topUp']);

        Route::get('reviews', [CustomerReviewController::class, 'index']);
        Route::post('reviews', [CustomerReviewController::class, 'store']);
        Route::post('ratings', [CustomerReviewController::class, 'storeRating']);

        Route::get('coupons', [CustomerCouponController::class, 'index']);

        Route::get('notifications', [CustomerMiscController::class, 'notifications']);
        Route::get('referral', [CustomerMiscController::class, 'referral']);
        Route::get('gift-cards', [CustomerMiscController::class, 'giftCards']);
        Route::post('gift-cards/purchase', [CustomerMiscController::class, 'purchaseGiftCard']);
        Route::post('complaints', [CustomerMiscController::class, 'complaints']);
        Route::post('sos', [CustomerMiscController::class, 'sos']);
    });
});

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
                'status' => true,
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

// Legacy ArroPay routes (payment gateway)
Route::post('v2/auth/login', [\App\Http\Controllers\ArroPayV2AuthApiController::class, 'login']);

Route::prefix('v1/disbursement')->group(function () {
    Route::post('banks', [\App\Http\Controllers\ArroPayDisbursementController::class, 'banks']);
    Route::post('initiatebankwithdraw', [\App\Http\Controllers\ArroPayDisbursementController::class, 'initiateBankWithdraw']);
    Route::post('processbankwithdraw', [\App\Http\Controllers\ArroPayDisbursementController::class, 'processBankWithdraw']);
});
