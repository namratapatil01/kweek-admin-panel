<?php

use App\Http\Controllers\Api\Customer\CustomerAuthController;
use App\Http\Controllers\Api\Customer\CustomerCashbackController;
use App\Http\Controllers\Api\Customer\CustomerCatalogController;
use App\Http\Controllers\Api\Customer\CustomerChatController;
use App\Http\Controllers\Api\Customer\CustomerCouponController;
use App\Http\Controllers\Api\Customer\CustomerDashboardController;
use App\Http\Controllers\Api\Customer\CustomerFavoriteController;
use App\Http\Controllers\Api\Customer\CustomerMiscController;
use App\Http\Controllers\Api\Customer\CustomerOrderController;
use App\Http\Controllers\Api\Customer\CustomerProfileController;
use App\Http\Controllers\Api\Customer\CustomerReviewController;
use App\Http\Controllers\Api\Customer\CustomerSettingsController;
use App\Http\Controllers\Api\Customer\CustomerTrackingController;
use App\Http\Controllers\Api\Customer\CustomerUploadController;
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
use App\Http\Controllers\Api\Driver\DriverAuthController;
use App\Http\Controllers\Api\Driver\DriverChatController;
use App\Http\Controllers\Api\Driver\DriverDashboardController;
use App\Http\Controllers\Api\Driver\DriverMiscController;
use App\Http\Controllers\Api\Driver\DriverOrderController;
use App\Http\Controllers\Api\Driver\DriverOwnerController;
use App\Http\Controllers\Api\Driver\DriverProfileController;
use App\Http\Controllers\Api\Driver\DriverReviewController;
use App\Http\Controllers\Api\Driver\DriverSettingsController;
use App\Http\Controllers\Api\Driver\DriverTrackingController;
use App\Http\Controllers\Api\Driver\DriverUploadController;
use App\Http\Controllers\Api\Driver\DriverWalletController;
use App\Http\Controllers\Api\Vendor\VendorAdvertisementController;
use App\Http\Controllers\Api\Vendor\VendorAuthController;
use App\Http\Controllers\Api\Vendor\VendorChatController;
use App\Http\Controllers\Api\Vendor\VendorCouponController;
use App\Http\Controllers\Api\Vendor\VendorDashboardController;
use App\Http\Controllers\Api\Vendor\VendorDineInController;
use App\Http\Controllers\Api\Vendor\VendorDriverController;
use App\Http\Controllers\Api\Vendor\VendorMiscController;
use App\Http\Controllers\Api\Vendor\VendorOrderController;
use App\Http\Controllers\Api\Vendor\VendorProductController;
use App\Http\Controllers\Api\Vendor\VendorProfileController;
use App\Http\Controllers\Api\Vendor\VendorReviewController;
use App\Http\Controllers\Api\Vendor\VendorStoryController;
use App\Http\Controllers\Api\Vendor\VendorSubscriptionController;
use App\Http\Controllers\Api\Vendor\VendorWalletController;
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
    Route::post('auth/phone', [CustomerAuthController::class, 'loginWithPhone']);
    Route::post('password/forgot', [CustomerAuthController::class, 'forgotPassword']);
    Route::post('password/reset', [CustomerAuthController::class, 'resetPassword']);
    Route::get('home', [CustomerDashboardController::class, 'home']);
    Route::get('referral/validate', [CustomerMiscController::class, 'validateReferral']);

    Route::middleware(['auth:sanctum', 'app.role:customer'])->group(function () {
        Route::post('logout', [CustomerAuthController::class, 'logout']);
        Route::delete('account', [CustomerAuthController::class, 'deleteAccount']);

        Route::get('profile', [CustomerProfileController::class, 'show']);
        Route::put('profile', [CustomerProfileController::class, 'update']);
        Route::post('profile/image', [CustomerProfileController::class, 'uploadImage']);

        Route::get('dashboard', [CustomerDashboardController::class, 'dashboard']);

        Route::get('settings', [CustomerSettingsController::class, 'index']);
        Route::get('settings/payment', [CustomerSettingsController::class, 'payment']);
        Route::get('settings/languages', [CustomerSettingsController::class, 'languages']);
        Route::get('settings/delivery-charge', [CustomerSettingsController::class, 'deliveryCharge']);
        Route::get('settings/{key}', [CustomerSettingsController::class, 'show']);

        Route::get('sections', [CustomerCatalogController::class, 'sections']);
        Route::get('categories', [CustomerCatalogController::class, 'categories']);
        Route::get('vendors', [CustomerCatalogController::class, 'vendors']);
        Route::get('vendors/nearest', [CustomerCatalogController::class, 'nearestVendors']);
        Route::get('products', [CustomerCatalogController::class, 'products']);
        Route::get('services', [CustomerCatalogController::class, 'services']);
        Route::get('brands', [CustomerCatalogController::class, 'brands']);
        Route::get('search', [CustomerCatalogController::class, 'search']);
        Route::get('advertisements', [CustomerCatalogController::class, 'advertisements']);
        Route::get('banners', [CustomerCatalogController::class, 'banners']);
        Route::get('stories', [CustomerCatalogController::class, 'stories']);
        Route::get('zones', [CustomerCatalogController::class, 'zones']);
        Route::get('taxes', [CustomerCatalogController::class, 'taxes']);
        Route::get('parcel/categories', [CustomerCatalogController::class, 'parcelCategories']);
        Route::get('parcel/weights', [CustomerCatalogController::class, 'parcelWeights']);
        Route::get('cab/vehicle-types', [CustomerCatalogController::class, 'vehicleTypes']);
        Route::get('cab/popular-destinations', [CustomerCatalogController::class, 'popularDestinations']);
        Route::get('rental/vehicle-types', [CustomerCatalogController::class, 'rentalVehicleTypes']);
        Route::get('rental/packages', [CustomerCatalogController::class, 'rentalPackages']);
        Route::get('provider/workers', [CustomerCatalogController::class, 'providerWorkers']);
        Route::get('review-attributes', [CustomerCatalogController::class, 'reviewAttributes']);
        Route::get('vendor-attributes', [CustomerCatalogController::class, 'vendorAttributes']);
        Route::get('vendors/{vendorId}/cuisines', [CustomerCatalogController::class, 'vendorCuisines']);
        Route::get('catalog/{type}/{id}', [CustomerCatalogController::class, 'show'])
            ->where('type', 'vendor|product|service|category|provider-category|brand|worker')
            ->where('id', '[a-zA-Z0-9\-_]+');

        Route::get('orders', [CustomerOrderController::class, 'index']);
        Route::post('orders', [CustomerOrderController::class, 'store']);
        Route::get('orders/{type}/{id}', [CustomerOrderController::class, 'show'])
            ->where('type', 'vendor|parcel|rental|ride|provider|dine-in');
        Route::patch('orders/{type}/{id}/status', [CustomerOrderController::class, 'updateStatus'])
            ->where('type', 'vendor|parcel|rental|ride|provider|dine-in');

        Route::get('tracking/orders/{type}/{id}', [CustomerTrackingController::class, 'order'])
            ->where('type', 'vendor|parcel|rental|ride|provider|dine-in');
        Route::get('tracking/drivers/{driverId}', [CustomerTrackingController::class, 'driverLocation'])
            ->where('driverId', '[a-zA-Z0-9\-_]+');

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
        Route::get('reviews/vendor/{vendorId}', [CustomerReviewController::class, 'vendorReviews']);
        Route::get('reviews/service/{serviceId}', [CustomerReviewController::class, 'serviceReviews']);
        Route::post('reviews', [CustomerReviewController::class, 'store']);
        Route::post('ratings', [CustomerReviewController::class, 'storeRating']);

        Route::get('coupons', [CustomerCouponController::class, 'index']);

        Route::get('cashback', [CustomerCashbackController::class, 'index']);
        Route::get('cashback/redeemed', [CustomerCashbackController::class, 'redeemed']);
        Route::post('cashback/redeem', [CustomerCashbackController::class, 'redeem']);

        Route::get('chat/{type}/inbox', [CustomerChatController::class, 'inbox'])
            ->where('type', 'store|driver|provider|worker');
        Route::get('chat/{type}/{orderId}/messages', [CustomerChatController::class, 'messages'])
            ->where('type', 'store|driver|provider|worker')
            ->where('orderId', '[a-zA-Z0-9\-_]+');
        Route::post('chat/{type}/send', [CustomerChatController::class, 'send'])
            ->where('type', 'store|driver|provider|worker');
        Route::post('chat/upload', [CustomerChatController::class, 'upload']);

        Route::post('uploads', [CustomerUploadController::class, 'store']);

        Route::get('notifications', [CustomerMiscController::class, 'notifications']);
        Route::get('notifications/content/{type}', [CustomerMiscController::class, 'notificationContent']);
        Route::patch('notifications/{id}/read', [CustomerMiscController::class, 'markNotificationRead']);
        Route::get('referral', [CustomerMiscController::class, 'referral']);
        Route::post('referral', [CustomerMiscController::class, 'storeReferral']);
        Route::post('referral/rewards', [CustomerMiscController::class, 'processReferralRewards']);
        Route::get('gift-cards', [CustomerMiscController::class, 'giftCards']);
        Route::get('gift-cards/history', [CustomerMiscController::class, 'giftCardHistory']);
        Route::post('gift-cards/purchase', [CustomerMiscController::class, 'purchaseGiftCard']);
        Route::post('gift-cards/redeem', [CustomerMiscController::class, 'redeemGiftCard']);
        Route::get('email-templates/{type}', [CustomerMiscController::class, 'emailTemplate']);
        Route::get('complaints', [CustomerMiscController::class, 'getComplaint']);
        Route::post('complaints', [CustomerMiscController::class, 'storeComplaint']);
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

Route::prefix('driver')->group(function () {
    Route::post('register', [DriverAuthController::class, 'register']);
    Route::post('login', [DriverAuthController::class, 'login']);
    Route::post('auth/google', [DriverAuthController::class, 'loginWithGoogle']);
    Route::post('auth/apple', [DriverAuthController::class, 'loginWithApple']);
    Route::post('auth/phone', [DriverAuthController::class, 'loginWithPhone']);
    Route::post('auth/phone/send-otp', [DriverAuthController::class, 'sendPhoneOtp']);
    Route::post('auth/phone/verify-otp', [DriverAuthController::class, 'verifyPhoneOtp']);
    Route::post('password/forgot', [DriverAuthController::class, 'forgotPassword']);
    Route::post('password/reset', [DriverAuthController::class, 'resetPassword']);
    Route::get('home', [DriverDashboardController::class, 'home']);
    Route::get('terms', [DriverMiscController::class, 'terms']);
    Route::get('privacy', [DriverMiscController::class, 'privacy']);
    Route::get('catalog', [DriverMiscController::class, 'catalog']);
    Route::get('settings', [DriverSettingsController::class, 'index']);
    Route::get('settings/payment', [DriverSettingsController::class, 'payment']);
    Route::get('settings/languages', [DriverSettingsController::class, 'languages']);
    Route::get('settings/taxes', [DriverSettingsController::class, 'taxes']);
    Route::get('settings/{key}', [DriverSettingsController::class, 'show']);
    Route::get('catalog/vendor/{vendorId}', [DriverMiscController::class, 'vendor'])->where('vendorId', '[a-zA-Z0-9\-_]+');

    Route::middleware(['auth:sanctum', 'app.role:driver'])->group(function () {
        Route::post('logout', [DriverAuthController::class, 'logout']);
        Route::delete('account', [DriverAuthController::class, 'deleteAccount']);

        Route::get('profile', [DriverProfileController::class, 'show']);
        Route::put('profile', [DriverProfileController::class, 'update']);
        Route::post('profile/image', [DriverProfileController::class, 'uploadImage']);
        Route::put('availability', [DriverProfileController::class, 'setOnline']);
        Route::put('location', [DriverProfileController::class, 'updateLocation']);
        Route::put('bank-details', [DriverProfileController::class, 'updateBankDetails']);

        Route::get('dashboard', [DriverDashboardController::class, 'dashboard']);

        Route::get('orders', [DriverOrderController::class, 'index']);
        Route::get('orders/stream', [DriverOrderController::class, 'stream']);
        Route::get('orders/parcel/search', [DriverOrderController::class, 'searchParcel']);
        Route::get('orders/rental/search', [DriverOrderController::class, 'searchRental']);
        Route::get('orders/{type}/{id}', [DriverOrderController::class, 'show'])
            ->where('type', 'vendor|ride|parcel|rental')
            ->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('orders/{type}/{id}/accept', [DriverOrderController::class, 'accept'])
            ->where('type', 'vendor|ride|parcel|rental')
            ->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('orders/{type}/{id}/reject', [DriverOrderController::class, 'reject'])
            ->where('type', 'vendor|ride|parcel|rental')
            ->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('orders/{type}/{id}/start', [DriverOrderController::class, 'start'])
            ->where('type', 'vendor|ride|parcel|rental')
            ->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('orders/{type}/{id}/complete', [DriverOrderController::class, 'complete'])
            ->where('type', 'vendor|ride|parcel|rental')
            ->where('id', '[a-zA-Z0-9\-_]+');
        Route::patch('orders/{type}/{id}/status', [DriverOrderController::class, 'updateStatus'])
            ->where('type', 'vendor|ride|parcel|rental')
            ->where('id', '[a-zA-Z0-9\-_]+');

        Route::get('wallet', [DriverWalletController::class, 'balance']);
        Route::get('wallet/transactions', [DriverWalletController::class, 'transactions']);
        Route::post('wallet/topup', [DriverWalletController::class, 'topUp']);
        Route::get('earnings', [DriverWalletController::class, 'earnings']);
        Route::post('wallet/withdraw', [DriverWalletController::class, 'withdraw']);
        Route::get('wallet/payouts', [DriverWalletController::class, 'payoutHistory']);
        Route::get('withdraw-method', [DriverWalletController::class, 'getWithdrawMethod']);
        Route::put('withdraw-method', [DriverWalletController::class, 'saveWithdrawMethod']);

        Route::get('chat/inbox', [DriverChatController::class, 'inbox']);
        Route::get('chat/restaurant/inbox', [DriverChatController::class, 'restaurantInbox']);
        Route::get('chat/{orderId}/messages', [DriverChatController::class, 'messages'])->where('orderId', '[a-zA-Z0-9\-_]+');
        Route::get('chat/restaurant/{orderId}/messages', [DriverChatController::class, 'restaurantMessages'])->where('orderId', '[a-zA-Z0-9\-_]+');
        Route::post('chat/send', [DriverChatController::class, 'send']);
        Route::post('chat/restaurant/send', [DriverChatController::class, 'sendRestaurant']);
        Route::post('chat/upload', [DriverChatController::class, 'upload']);

        Route::get('tracking/orders/{type}/{id}', [DriverTrackingController::class, 'order'])
            ->where('type', 'vendor|ride|parcel|rental')
            ->where('id', '[a-zA-Z0-9\-_]+');

        Route::post('uploads', [DriverUploadController::class, 'store']);

        Route::get('reviews', [DriverReviewController::class, 'index']);
        Route::post('reviews', [DriverReviewController::class, 'store']);
        Route::get('reviews/order/{orderId}', [DriverReviewController::class, 'forOrder'])->where('orderId', '[a-zA-Z0-9\-_]+');
        Route::get('ratings', [DriverReviewController::class, 'ratings']);

        Route::get('notifications', [DriverMiscController::class, 'notifications']);
        Route::get('notifications/content/{type}', [DriverMiscController::class, 'notificationContent']);
        Route::patch('notifications/{id}/read', [DriverMiscController::class, 'markNotificationRead']);
        Route::get('documents', [DriverMiscController::class, 'documents']);
        Route::get('documents/status', [DriverMiscController::class, 'documentStatus']);
        Route::post('documents', [DriverMiscController::class, 'submitDocuments']);
        Route::post('documents/upload', [DriverMiscController::class, 'uploadDocument']);

        Route::get('owner/drivers', [DriverOwnerController::class, 'index']);
        Route::get('owner/dashboard', [DriverOwnerController::class, 'dashboard']);
        Route::get('owner/drivers/locations', [DriverOwnerController::class, 'locations']);
        Route::post('owner/drivers', [DriverOwnerController::class, 'store']);
        Route::get('owner/drivers/{id}', [DriverOwnerController::class, 'show'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::put('owner/drivers/{id}', [DriverOwnerController::class, 'update'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::delete('owner/drivers/{id}', [DriverOwnerController::class, 'destroy'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('owner/drivers/{id}/image', [DriverOwnerController::class, 'uploadImage'])->where('id', '[a-zA-Z0-9\-_]+');
    });
});

Route::prefix('vendor')->group(function () {
    Route::post('register', [VendorAuthController::class, 'register']);
    Route::post('login', [VendorAuthController::class, 'login']);
    Route::post('auth/google', [VendorAuthController::class, 'loginWithGoogle']);
    Route::post('auth/apple', [VendorAuthController::class, 'loginWithApple']);
    Route::post('auth/phone', [VendorAuthController::class, 'loginWithPhone']);
    Route::post('password/forgot', [VendorAuthController::class, 'forgotPassword']);
    Route::post('password/reset', [VendorAuthController::class, 'resetPassword']);
    Route::get('home', [VendorDashboardController::class, 'home']);
    Route::get('terms', [VendorMiscController::class, 'terms']);
    Route::get('privacy', [VendorMiscController::class, 'privacy']);
    Route::get('catalog', [VendorMiscController::class, 'catalog']);
    Route::get('subscriptions/plans', [VendorSubscriptionController::class, 'plans']);

    Route::middleware(['auth:sanctum', 'app.role:vendor'])->group(function () {
        Route::post('logout', [VendorAuthController::class, 'logout']);
        Route::delete('account', [VendorAuthController::class, 'deleteAccount']);

        Route::get('profile', [VendorProfileController::class, 'show']);
        Route::put('profile', [VendorProfileController::class, 'update']);
        Route::post('profile/image', [VendorProfileController::class, 'uploadImage']);
        Route::put('bank-details', [VendorProfileController::class, 'updateBankDetails']);

        Route::get('store', [VendorProfileController::class, 'showStore']);
        Route::post('store', [VendorProfileController::class, 'createStore']);
        Route::put('store', [VendorProfileController::class, 'updateStore']);
        Route::post('store/image', [VendorProfileController::class, 'uploadStoreImage']);

        Route::get('dashboard', [VendorDashboardController::class, 'dashboard']);

        Route::get('orders', [VendorOrderController::class, 'index']);
        Route::get('orders/{id}', [VendorOrderController::class, 'show'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('orders/{id}/accept', [VendorOrderController::class, 'accept'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('orders/{id}/reject', [VendorOrderController::class, 'reject'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('orders/{id}/cancel', [VendorOrderController::class, 'cancel'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('orders/{id}/complete', [VendorOrderController::class, 'complete'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('orders/{id}/assign-driver', [VendorOrderController::class, 'assignDriver'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('orders/{id}/ship', [VendorOrderController::class, 'ship'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::patch('orders/{id}', [VendorOrderController::class, 'update'])->where('id', '[a-zA-Z0-9\-_]+');

        Route::get('products', [VendorProductController::class, 'index']);
        Route::post('products', [VendorProductController::class, 'store']);
        Route::get('products/{id}', [VendorProductController::class, 'show'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::put('products/{id}', [VendorProductController::class, 'update'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::delete('products/{id}', [VendorProductController::class, 'destroy'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('products/{id}/images', [VendorProductController::class, 'uploadImages'])->where('id', '[a-zA-Z0-9\-_]+');

        Route::get('coupons', [VendorCouponController::class, 'index']);
        Route::post('coupons', [VendorCouponController::class, 'store']);
        Route::get('coupons/{id}', [VendorCouponController::class, 'show'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::put('coupons/{id}', [VendorCouponController::class, 'update'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::delete('coupons/{id}', [VendorCouponController::class, 'destroy'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('coupons/{id}/image', [VendorCouponController::class, 'uploadImage'])->where('id', '[a-zA-Z0-9\-_]+');

        Route::get('wallet', [VendorWalletController::class, 'balance']);
        Route::get('wallet/transactions', [VendorWalletController::class, 'transactions']);
        Route::get('earnings', [VendorWalletController::class, 'earnings']);
        Route::post('wallet/withdraw', [VendorWalletController::class, 'withdraw']);
        Route::get('wallet/payouts', [VendorWalletController::class, 'payoutHistory']);
        Route::get('withdraw-method', [VendorWalletController::class, 'getWithdrawMethod']);
        Route::put('withdraw-method', [VendorWalletController::class, 'saveWithdrawMethod']);

        Route::get('chat/inbox', [VendorChatController::class, 'inbox']);
        Route::get('chat/{orderId}/messages', [VendorChatController::class, 'messages'])->where('orderId', '[a-zA-Z0-9\-_]+');
        Route::post('chat/send', [VendorChatController::class, 'send']);
        Route::post('chat/upload', [VendorChatController::class, 'upload']);

        Route::get('reviews', [VendorReviewController::class, 'index']);
        Route::get('reviews/order/{orderId}', [VendorReviewController::class, 'forOrder'])->where('orderId', '[a-zA-Z0-9\-_]+');
        Route::get('ratings', [VendorReviewController::class, 'ratings']);

        Route::get('drivers', [VendorDriverController::class, 'index']);
        Route::post('drivers', [VendorDriverController::class, 'store']);
        Route::get('drivers/{id}', [VendorDriverController::class, 'show'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::put('drivers/{id}', [VendorDriverController::class, 'update'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('drivers/{id}/image', [VendorDriverController::class, 'uploadImage'])->where('id', '[a-zA-Z0-9\-_]+');

        Route::get('dine-in/bookings', [VendorDineInController::class, 'bookings']);
        Route::get('dine-in/bookings/{id}', [VendorDineInController::class, 'show'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('dine-in/bookings/{id}/accept', [VendorDineInController::class, 'accept'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('dine-in/bookings/{id}/reject', [VendorDineInController::class, 'reject'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::put('dine-in/config', [VendorDineInController::class, 'updateConfig']);

        Route::get('subscriptions/history', [VendorSubscriptionController::class, 'history']);
        Route::post('subscriptions', [VendorSubscriptionController::class, 'subscribe']);

        Route::get('advertisements', [VendorAdvertisementController::class, 'index']);
        Route::post('advertisements', [VendorAdvertisementController::class, 'store']);
        Route::get('advertisements/{id}', [VendorAdvertisementController::class, 'show'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::put('advertisements/{id}', [VendorAdvertisementController::class, 'update'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::delete('advertisements/{id}', [VendorAdvertisementController::class, 'destroy'])->where('id', '[a-zA-Z0-9\-_]+');
        Route::post('advertisements/{id}/media', [VendorAdvertisementController::class, 'uploadMedia'])->where('id', '[a-zA-Z0-9\-_]+');

        Route::get('story', [VendorStoryController::class, 'show']);
        Route::post('story', [VendorStoryController::class, 'store']);
        Route::delete('story', [VendorStoryController::class, 'destroy']);
        Route::post('story/upload', [VendorStoryController::class, 'uploadMedia']);

        Route::get('notifications', [VendorMiscController::class, 'notifications']);
        Route::get('documents', [VendorMiscController::class, 'documents']);
        Route::get('documents/status', [VendorMiscController::class, 'documentStatus']);
        Route::post('documents', [VendorMiscController::class, 'submitDocuments']);
        Route::post('documents/upload', [VendorMiscController::class, 'uploadDocument']);
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
