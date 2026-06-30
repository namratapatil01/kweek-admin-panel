<?php

use Illuminate\Support\Facades\Route;

/**
 * MySQL admin resource routes — replaces Firebase client-side CRUD.
 */
$moduleControllers = [
    'sections' => \App\Http\Controllers\SectionController::class,
    'taxes' => \App\Http\Controllers\TaxController::class,
    'currencies' => \App\Http\Controllers\CurrencyController::class,
    'brands' => \App\Http\Controllers\BrandController::class,
    'vendor-categories' => \App\Http\Controllers\CategoryController::class,
    'vendor-filters' => \App\Http\Controllers\VendorFiltersController::class,
    'vendor-attributes' => \App\Http\Controllers\AttributeController::class,
    'documents' => \App\Http\Controllers\DocumentController::class,
    'review-attributes' => \App\Http\Controllers\ReviewAttributeController::class,
    'coupons' => \App\Http\Controllers\CouponController::class,
    'subscription-plans' => \App\Http\Controllers\SubscriptionPlanController::class,
    'vehicle-types' => \App\Http\Controllers\VehicleTypeController::class,
    'rental-vehicle-types' => \App\Http\Controllers\RentalVehicleTypeController::class,
    'rental-packages' => \App\Http\Controllers\RentalPackageController::class,
    'parcel-categories' => \App\Http\Controllers\ParcelCategoryController::class,
    'parcel-weights' => \App\Http\Controllers\ParcelWeightController::class,
    'gift-cards' => \App\Http\Controllers\GiftCardController::class,
    'cms-pages' => \App\Http\Controllers\CmsPageController::class,
    'on-boarding' => \App\Http\Controllers\OnBoardingController::class,
    'popular-destinations' => \App\Http\Controllers\PopularDestinationController::class,
    'banner-items' => \App\Http\Controllers\BannerController::class,
    'advertisements' => \App\Http\Controllers\AdvertisementsController::class,
    'vendor-orders' => \App\Http\Controllers\OrderController::class,
    'rides' => \App\Http\Controllers\RideController::class,
    'vendors' => \App\Http\Controllers\VendorController::class,
    'users' => \App\Http\Controllers\CustomerController::class,
    'zones' => \App\Http\Controllers\ZoneController::class,
    'notifications' => \App\Http\Controllers\NotificationController::class,
    'dynamic-notifications' => \App\Http\Controllers\DynamicNotificationController::class,
    'wallet-transactions' => \App\Http\Controllers\WalletTransactionController::class,
    'parcel-orders' => \App\Http\Controllers\ParcelOrderController::class,
    'rental-orders' => \App\Http\Controllers\RentalOrderController::class,
    'provider-orders' => \App\Http\Controllers\ProviderOrderController::class,
    'payouts' => \App\Http\Controllers\PayoutController::class,
    'driver-payouts' => \App\Http\Controllers\DriverPayoutController::class,
    'complaints' => \App\Http\Controllers\ComplaintController::class,
    'sos' => \App\Http\Controllers\SosController::class,
    'referrals' => \App\Http\Controllers\ReferralController::class,
    'stories' => \App\Http\Controllers\StoryController::class,
    'email-templates' => \App\Http\Controllers\EmailTemplateController::class,
    'vendor-products' => \App\Http\Controllers\VendorProductController::class,
    'provider-categories' => \App\Http\Controllers\ProviderCategoryController::class,
    'provider-services' => \App\Http\Controllers\ProviderServiceController::class,
    'provider-workers' => \App\Http\Controllers\ProviderWorkerController::class,
    'subscription-histories' => \App\Http\Controllers\SubscriptionHistoryController::class,
    'booked-tables' => \App\Http\Controllers\BookedTableController::class,
    'item-reviews' => \App\Http\Controllers\ItemReviewController::class,
    'promos' => \App\Http\Controllers\PromoController::class,
    'car-makes' => \App\Http\Controllers\CarMakeController::class,
    'car-models' => \App\Http\Controllers\CarModelController::class,
];

Route::middleware(['auth'])->group(function () use ($moduleControllers) {
    foreach ($moduleControllers as $slug => $controller) {
        if (! class_exists($controller)) {
            continue;
        }

        $config = config("admin_modules.{$slug}", []);
        $routeName = $config['route'] ?? str_replace('_', '-', $slug);
        $permission = $config['permission'] ?? $slug;
        $indexRouteName = $config['index_route'] ?? "{$routeName}.index";
        $legacyRouteName = $config['legacy_route'] ?? null;

        Route::middleware(["permission:{$permission},{$permission}"])->group(function () use ($controller, $routeName, $indexRouteName, $legacyRouteName) {
            Route::get("{$routeName}/datatable", [$controller, 'datatable'])->name("{$routeName}.datatable");
            Route::resource($routeName, $controller)->names([
                'index' => $indexRouteName,
            ]);
            if ($legacyRouteName && $legacyRouteName !== $indexRouteName) {
                Route::get($routeName, [$controller, 'index'])->name($legacyRouteName);
            }
            Route::post("{$routeName}/bulk-delete", [$controller, 'destroy'])->name("{$routeName}.bulk-destroy");
        });
    }
});
