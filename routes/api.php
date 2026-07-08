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
use App\Http\Controllers\Api\Provider\ProviderAuthController;
use App\Http\Controllers\Api\Provider\ProviderBookingController;
use App\Http\Controllers\Api\Provider\ProviderChatController;
use App\Http\Controllers\Api\Provider\ProviderCouponController;
use App\Http\Controllers\Api\Provider\ProviderDashboardController;
use App\Http\Controllers\Api\Provider\ProviderMiscController;
use App\Http\Controllers\Api\Provider\ProviderProfileController;
use App\Http\Controllers\Api\Provider\ProviderReviewController;
use App\Http\Controllers\Api\Provider\ProviderServiceController;
use App\Http\Controllers\Api\Provider\ProviderSubscriptionController;
use App\Http\Controllers\Api\Provider\ProviderWalletController;
use App\Http\Controllers\Api\Provider\ProviderWorkerController;
use App\Http\Controllers\Api\Worker\WorkerAuthController;
use App\Http\Controllers\Api\Worker\WorkerChatController;
use App\Http\Controllers\Api\Worker\WorkerDashboardController;
use App\Http\Controllers\Api\Worker\WorkerJobController;
use App\Http\Controllers\Api\Worker\WorkerMiscController;
use App\Http\Controllers\Api\Worker\WorkerProfileController;
use App\Http\Controllers\Api\Worker\WorkerReviewController;
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

Route::prefix('provider')->group(function () {
    Route::post('register', [ProviderAuthController::class, 'register']);
    Route::post('login', [ProviderAuthController::class, 'login']);
    Route::post('auth/apple', [ProviderAuthController::class, 'loginWithApple']);
    Route::post('auth/phone', [ProviderAuthController::class, 'loginWithPhone']);
    Route::post('password/forgot', [ProviderAuthController::class, 'forgotPassword']);
    Route::post('password/reset', [ProviderAuthController::class, 'resetPassword']);
    Route::get('home', [ProviderDashboardController::class, 'home']);
    Route::get('terms', [ProviderMiscController::class, 'terms']);
    Route::get('privacy', [ProviderMiscController::class, 'privacy']);

    Route::middleware(['auth:sanctum', 'app.role:provider'])->group(function () {
        Route::post('logout', [ProviderAuthController::class, 'logout']);
        Route::delete('account', [ProviderAuthController::class, 'deleteAccount']);

        Route::get('profile', [ProviderProfileController::class, 'show']);
        Route::put('profile', [ProviderProfileController::class, 'update']);
        Route::post('profile/image', [ProviderProfileController::class, 'uploadImage']);
        Route::put('bank-details', [ProviderProfileController::class, 'updateBankDetails']);

        Route::get('dashboard', [ProviderDashboardController::class, 'dashboard']);

        Route::get('sections', [ProviderServiceController::class, 'sections']);
        Route::get('categories', [ProviderServiceController::class, 'categories']);

        Route::get('services', [ProviderServiceController::class, 'index']);
        Route::post('services', [ProviderServiceController::class, 'store']);
        Route::get('services/{id}', [ProviderServiceController::class, 'show'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::put('services/{id}', [ProviderServiceController::class, 'update'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::delete('services/{id}', [ProviderServiceController::class, 'destroy'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('services/{id}/images', [ProviderServiceController::class, 'uploadImages'])->where('id', '[a-zA-Z0-9\-_]+');

        Route::get('bookings', [ProviderBookingController::class, 'index']);
        Route::get('bookings/{id}', [ProviderBookingController::class, 'show'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('bookings/{id}/accept', [ProviderBookingController::class, 'accept'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('bookings/{id}/reject', [ProviderBookingController::class, 'reject'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('bookings/{id}/assign-worker', [ProviderBookingController::class, 'assignWorker'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('bookings/{id}/start', [ProviderBookingController::class, 'start'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('bookings/{id}/stop-timer', [ProviderBookingController::class, 'stopTimer'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('bookings/{id}/extra-charges', [ProviderBookingController::class, 'extraCharges'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('bookings/{id}/complete', [ProviderBookingController::class, 'complete'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::patch('bookings/{id}/status', [ProviderBookingController::class, 'updateStatus'])->where('id', '[a-zA-Z0-9\-_]+');

        Route::get('workers', [ProviderWorkerController::class, 'index']);
        Route::post('workers', [ProviderWorkerController::class, 'store']);
        Route::get('workers/{id}', [ProviderWorkerController::class, 'show'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::put('workers/{id}', [ProviderWorkerController::class, 'update'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::delete('workers/{id}', [ProviderWorkerController::class, 'destroy'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('workers/{id}/image', [ProviderWorkerController::class, 'uploadImage'])->where('id', '[a-zA-Z0-9\-_]+');

        Route::get('coupons', [ProviderCouponController::class, 'index']);
        Route::post('coupons', [ProviderCouponController::class, 'store']);
        Route::get('coupons/{id}', [ProviderCouponController::class, 'show'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::put('coupons/{id}', [ProviderCouponController::class, 'update'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::delete('coupons/{id}', [ProviderCouponController::class, 'destroy'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('coupons/{id}/image', [ProviderCouponController::class, 'uploadImage'])->where('id', '[a-zA-Z0-9\-_]+');

        Route::get('wallet', [ProviderWalletController::class, 'balance']);
        Route::get('wallet/transactions', [ProviderWalletController::class, 'transactions']);
        Route::get('earnings', [ProviderWalletController::class, 'earnings']);
        Route::post('wallet/withdraw', [ProviderWalletController::class, 'withdraw']);
        Route::get('wallet/payouts', [ProviderWalletController::class, 'payoutHistory']);
        Route::get('withdraw-method', [ProviderWalletController::class, 'getWithdrawMethod']);
        Route::put('withdraw-method', [ProviderWalletController::class, 'saveWithdrawMethod']);

        Route::get('subscriptions/plans', [ProviderSubscriptionController::class, 'plans']);
        Route::get('subscriptions/history', [ProviderSubscriptionController::class, 'history']);
        Route::post('subscriptions', [ProviderSubscriptionController::class, 'subscribe']);

        Route::get('chat/inbox', [ProviderChatController::class, 'inbox']);
        Route::get('chat/{orderId}/messages', [ProviderChatController::class, 'messages'])->where('orderId', '[a-zA-Z0-9\-_]+');
        Route::post('chat/send', [ProviderChatController::class, 'send']);
        Route::post('chat/upload', [ProviderChatController::class, 'upload']);

        Route::get('reviews', [ProviderReviewController::class, 'index']);
        Route::get('reviews/order/{orderId}', [ProviderReviewController::class, 'forOrder'])->where('orderId', '[a-zA-Z0-9\-_]+');
        Route::get('ratings', [ProviderReviewController::class, 'ratings']);

        Route::get('notifications', [ProviderMiscController::class, 'notifications']);
        Route::get('documents', [ProviderMiscController::class, 'documents']);
        Route::get('documents/status', [ProviderMiscController::class, 'documentStatus']);
        Route::post('documents', [ProviderMiscController::class, 'submitDocuments']);
        Route::post('documents/upload', [ProviderMiscController::class, 'uploadDocument']);
    });
});

Route::prefix('worker')->group(function () {
    Route::post('register', [WorkerAuthController::class, 'register']);
    Route::post('login', [WorkerAuthController::class, 'login']);
    Route::post('password/forgot', [WorkerAuthController::class, 'forgotPassword']);
    Route::post('password/reset', [WorkerAuthController::class, 'resetPassword']);
    Route::get('home', [WorkerDashboardController::class, 'home']);
    Route::get('terms', [WorkerMiscController::class, 'terms']);
    Route::get('privacy', [WorkerMiscController::class, 'privacy']);

    Route::middleware(['auth:sanctum', 'app.role:worker'])->group(function () {
        Route::post('logout', [WorkerAuthController::class, 'logout']);
        Route::delete('account', [WorkerAuthController::class, 'deleteAccount']);

        Route::get('profile', [WorkerProfileController::class, 'show']);
        Route::put('profile', [WorkerProfileController::class, 'update']);
        Route::post('profile/image', [WorkerProfileController::class, 'uploadImage']);
        Route::put('availability', [WorkerProfileController::class, 'setOnline']);
        Route::get('provider', [WorkerProfileController::class, 'provider']);

        Route::get('dashboard', [WorkerDashboardController::class, 'dashboard']);

        Route::get('jobs', [WorkerJobController::class, 'index']);
        Route::get('jobs/{id}', [WorkerJobController::class, 'show'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('jobs/{id}/accept', [WorkerJobController::class, 'accept'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('jobs/{id}/reject', [WorkerJobController::class, 'reject'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('jobs/{id}/start', [WorkerJobController::class, 'start'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('jobs/{id}/stop-timer', [WorkerJobController::class, 'stopTimer'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('jobs/{id}/extra-charges', [WorkerJobController::class, 'extraCharges'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('jobs/{id}/complete', [WorkerJobController::class, 'complete'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::patch('jobs/{id}/status', [WorkerJobController::class, 'updateStatus'])->where('id', '[a-zA-Z0-9\-_]+');

        Route::get('chat/inbox', [WorkerChatController::class, 'inbox']);
        Route::get('chat/{orderId}/messages', [WorkerChatController::class, 'messages'])->where('orderId', '[a-zA-Z0-9\-_]+');
        Route::post('chat/send', [WorkerChatController::class, 'send']);
        Route::post('chat/upload', [WorkerChatController::class, 'upload']);

        Route::get('reviews', [WorkerReviewController::class, 'index']);
        Route::get('reviews/order/{orderId}', [WorkerReviewController::class, 'forOrder'])->where('orderId', '[a-zA-Z0-9\-_]+');
        Route::get('ratings', [WorkerReviewController::class, 'ratings']);

        Route::get('earnings', [WorkerMiscController::class, 'earnings']);
        Route::get('notifications', [WorkerMiscController::class, 'notifications']);
        Route::get('documents', [WorkerMiscController::class, 'documents']);
        Route::get('documents/status', [WorkerMiscController::class, 'documentStatus']);
        Route::post('documents', [WorkerMiscController::class, 'submitDocuments']);
        Route::post('documents/upload', [WorkerMiscController::class, 'uploadDocument']);
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
