<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Complete KWEEK schema migrated from Firebase Firestore.
 *
 * PK strategy: string document IDs (Firebase-compatible, max 64 chars).
 * Hybrid storage: indexed scalar columns + JSON payload for nested/array fields.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->dropLegacyStubTables();

        Schema::create('sections', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('name')->nullable();
            $table->string('serviceType', 64)->nullable()->index();
            $table->string('serviceTypeFlag', 64)->nullable();
            $table->boolean('isActive')->default(true)->index();
            $table->string('sectionImage')->nullable();
            $table->string('color', 32)->nullable();
            $table->unsignedInteger('nearByRadius')->nullable();
            $table->unsignedInteger('delivery_charge')->nullable();
            $table->string('adminCommision', 32)->nullable();
            $table->boolean('dine_in_active')->default(false);
            $table->string('rideType', 64)->nullable();
            $table->boolean('is_product_details')->default(false);
            $table->string('cab_service_template', 64)->nullable();
            $table->boolean('enableCashbackOffer')->default(false);
            $table->string('theme', 64)->nullable();
            $table->unsignedInteger('referralAmount')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('id', 128)->primary();
            $table->json('value');
            $table->timestamps();
        });

        Schema::create('currencies', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('country')->nullable();
            $table->string('name')->nullable();
            $table->string('symbol', 16)->nullable();
            $table->string('code', 8)->nullable()->index();
            $table->boolean('isActive')->default(false)->index();
            $table->boolean('symbolAtRight')->default(false);
            $table->unsignedTinyInteger('decimal_degits')->default(2);
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('app_users', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phoneNumber', 32)->nullable()->index();
            $table->string('password')->nullable();
            $table->string('role', 32)->nullable()->index();
            $table->boolean('active')->default(true)->index();
            $table->boolean('isActive')->default(true);
            $table->boolean('isOwner')->default(false);
            $table->boolean('isDocumentVerify')->default(false);
            $table->string('profilePictureURL')->nullable();
            $table->string('sectionId', 64)->nullable()->index();
            $table->string('section_id', 64)->nullable()->index();
            $table->string('vendorID', 64)->nullable()->index();
            $table->string('ownerId', 64)->nullable()->index();
            $table->string('serviceType', 64)->nullable();
            $table->string('rideType', 64)->nullable();
            $table->string('vehicleType', 64)->nullable();
            $table->string('vehicleId', 64)->nullable();
            $table->string('zoneId', 64)->nullable()->index();
            $table->string('countryCode', 8)->nullable();
            $table->decimal('wallet_amount', 15, 2)->default(0);
            $table->unsignedInteger('orderCompleted')->default(0);
            $table->string('fcmToken')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('carName')->nullable();
            $table->string('carNumber', 32)->nullable();
            $table->string('carColor', 32)->nullable();
            $table->string('carPictureURL')->nullable();
            $table->string('carProofPictureURL')->nullable();
            $table->string('driverProofPictureURL')->nullable();
            $table->string('driverRate', 32)->nullable();
            $table->timestamp('createdAt')->nullable()->index();
            $table->timestamp('lastOnlineTimestamp')->nullable();
            $table->json('userBankDetails')->nullable();
            $table->json('settings')->nullable();
            $table->json('shippingAddress')->nullable();
            $table->json('carInfo')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['role', 'isActive']);
        });

        Schema::create('vendors', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('title')->nullable()->index();
            $table->text('description')->nullable();
            $table->string('photo')->nullable();
            $table->string('categoryPhoto')->nullable();
            $table->string('section_id', 64)->nullable()->index();
            $table->string('zoneId', 64)->nullable()->index();
            $table->string('categoryID', 64)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phonenumber', 32)->nullable();
            $table->boolean('reststatus')->default(true);
            $table->decimal('walletAmount', 15, 2)->default(0);
            $table->decimal('reviewsSum', 10, 2)->default(0);
            $table->unsignedInteger('reviewsCount')->default(0);
            $table->decimal('adminCommission', 8, 2)->nullable();
            $table->boolean('dine_in_active')->default(false);
            $table->timestamp('createdAt')->nullable();
            $table->json('photos')->nullable();
            $table->json('workingHours')->nullable();
            $table->json('filters')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        $this->createOrderTable('vendor_orders', [
            'vendorID' => true,
            'driverID' => true,
            'takeAway' => 'boolean',
            'scheduleTime' => 'timestamp',
        ]);

        $this->createOrderTable('parcel_orders', [
            'driverId' => true,
            'driverID' => true,
            'parcelCategoryID' => true,
            'sectionId' => true,
        ]);

        $this->createOrderTable('rental_orders', [
            'driverId' => true,
            'vehicleId' => true,
            'paymentMethod' => 'string',
        ]);

        $this->createOrderTable('provider_orders', [
            'workerId' => true,
            'payment_method' => 'string',
        ]);

        $this->createOrderTable('rides', [
            'driverId' => true,
            'vehicleId' => true,
            'paymentMethod' => 'string',
            'scheduleDateTime' => 'timestamp',
        ]);

        Schema::create('zones', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('name')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('area')->nullable();
            $table->boolean('publish')->default(true)->index();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('title')->nullable();
            $table->string('type', 32)->nullable()->index();
            $table->boolean('frontSide')->default(false);
            $table->boolean('backSide')->default(false);
            $table->boolean('enable')->default(true)->index();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('documents_verify', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('type', 32)->nullable();
            $table->json('documents')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('wallet', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('user_id', 64)->nullable()->index();
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('note')->nullable();
            $table->boolean('isTopUp')->default(true);
            $table->string('payment_method', 64)->nullable();
            $table->string('payment_status', 32)->default('success')->index();
            $table->string('transactionUser', 64)->nullable();
            $table->string('order_id', 64)->nullable()->index();
            $table->string('subscription_id', 64)->nullable();
            $table->string('serviceType', 64)->nullable();
            $table->timestamp('date')->nullable()->index();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('userId', 64)->nullable()->index();
            $table->string('userType', 32)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('note')->nullable();
            $table->string('orderType', 64)->nullable();
            $table->string('paymentType', 64)->nullable();
            $table->string('transactionId', 64)->nullable();
            $table->timestamp('createdDate')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('driver_payouts', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('driverID', 64)->nullable()->index();
            $table->string('driverId', 64)->nullable()->index();
            $table->string('vendorID', 64)->nullable()->index();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('paymentStatus', 32)->nullable()->index();
            $table->string('role', 32)->nullable();
            $table->text('note')->nullable();
            $table->text('adminNote')->nullable();
            $table->string('withdrawMethod', 64)->nullable();
            $table->timestamp('paidDate')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('payouts', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('vendorID', 64)->nullable()->index();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('paymentStatus', 32)->nullable()->index();
            $table->string('role', 32)->nullable();
            $table->text('note')->nullable();
            $table->text('adminNote')->nullable();
            $table->string('withdrawMethod', 64)->nullable();
            $table->timestamp('paidDate')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('name')->nullable();
            $table->string('flag', 32)->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        $this->createCatalogTable('vehicle_types');
        $this->createCatalogTable('rental_vehicle_types');
        $this->createCatalogTable('car_makes');
        $this->createCatalogTable('car_models', ['car_make_id' => true, 'car_make_name' => 'string']);
        $this->createCatalogTable('vendor_categories', ['section_id' => true]);
        $this->createCatalogTable('vendor_products', ['vendorID' => true, 'section_id' => true, 'categoryID' => true]);
        $this->createCatalogTable('vendor_attributes');
        $this->createCatalogTable('subscription_plans', ['sectionId' => true]);
        $this->createCatalogTable('subscription_histories', ['user_id' => true]);
        $this->createCatalogTable('coupons', ['vendorID' => true, 'section_id' => true]);
        $this->createCatalogTable('parcel_coupons', ['sectionId' => true]);
        $this->createCatalogTable('rental_coupons', ['sectionId' => true]);
        $this->createCatalogTable('providers_coupons', ['sectionId' => true, 'providerId' => true]);
        $this->createCatalogTable('promos', ['sectionId' => true]);
        $this->createCatalogTable('taxes', ['sectionId' => true]);
        $this->createCatalogTable('brands', ['sectionId' => true]);
        $this->createCatalogTable('parcel_categories', ['sectionId' => true]);
        $this->createCatalogTable('parcel_weights');
        $this->createCatalogTable('provider_categories', ['sectionId' => true, 'parentCategoryId' => true]);
        $this->createCatalogTable('providers_services', ['sectionId' => true, 'categoryId' => true]);
        $this->createCatalogTable('providers_workers', ['providerId' => true]);
        $this->createCatalogTable('rental_packages', ['sectionId' => true, 'vehicleTypeId' => true]);
        $this->createCatalogTable('review_attributes');
        $this->createCatalogTable('email_templates', ['type' => 'string']);
        $this->createCatalogTable('dynamic_notifications', ['type' => 'string', 'service_type' => 'string']);
        $this->createCatalogTable('cms_pages', ['slug' => 'string']);
        $this->createCatalogTable('on_boarding', ['type' => 'string']);
        $this->createCatalogTable('gift_cards');
        $this->createCatalogTable('gift_purchases', ['giftId' => true, 'userid' => true]);
        $this->createCatalogTable('popular_destinations', ['sectionId' => true]);
        $this->createCatalogTable('banner_items', ['sectionId' => true]);
        $this->createCatalogTable('advertisements', ['sectionId' => true, 'vendorId' => true]);
        $this->createCatalogTable('complaints', ['orderId' => true, 'driverId' => true, 'customerId' => true]);
        $this->createCatalogTable('sos', ['orderId' => true]);
        $this->createCatalogTable('booked_tables', ['vendorID' => true, 'authorID' => true, 'section_id' => true]);
        $this->createCatalogTable('order_transactions', ['order_id' => true, 'driverId' => true, 'vendorId' => true]);
        $this->createCatalogTable('items_reviews', ['orderid' => true, 'productId' => true, 'VendorId' => true]);
        $this->createCatalogTable('ratings', ['orderid' => true]);
        $this->createCatalogTable('referrals', ['referralBy' => true]);
        $this->createCatalogTable('withdraw_methods', ['userId' => true]);
        $this->createCatalogTable('stories', ['vendorID' => true, 'sectionID' => true]);
        $this->createCatalogTable('notifications', ['role' => 'string']);

        $this->createPivotTable('favorite_vendors', ['store_id', 'user_id', 'section_id']);
        $this->createPivotTable('favorite_items', ['product_id', 'store_id', 'user_id', 'section_id']);
        $this->createPivotTable('favorite_services', ['service_id', 'user_id', 'section_id', 'service_author_id']);
        $this->createPivotTable('favorite_providers', ['provider_id', 'user_id', 'section_id']);

        foreach (['chat_admin', 'chat_driver', 'chat_provider', 'chat_store', 'chat_worker'] as $chatTable) {
            Schema::create($chatTable, function (Blueprint $table) {
                $table->string('id', 64)->primary();
                $table->string('orderId', 64)->nullable()->index();
                $table->string('customerId', 64)->nullable()->index();
                $table->string('restaurantId', 64)->nullable()->index();
                $table->string('lastSenderId', 64)->nullable();
                $table->string('chatType', 32)->nullable();
                $table->text('lastMessage')->nullable();
                $table->timestamp('createdAt')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('type', 64)->nullable()->index();
            $table->string('source')->nullable();
            $table->string('dest')->nullable();
            $table->timestamp('createdAt')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('heartbeats', function (Blueprint $table) {
            $table->id();
            $table->timestamp('timestamp')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role_id')) {
                $table->unsignedBigInteger('role_id')->nullable()->after('password');
            }
        });

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            if (! Schema::hasColumn('personal_access_tokens', 'tokenable_type')) {
                return;
            }
        });
    }

    public function down(): void
    {
        $tables = [
            'heartbeats', 'reports',
            'chat_worker', 'chat_store', 'chat_provider', 'chat_driver', 'chat_admin',
            'favorite_providers', 'favorite_services', 'favorite_items', 'favorite_vendors',
            'notifications', 'stories', 'withdraw_methods', 'referrals', 'ratings',
            'items_reviews', 'order_transactions', 'booked_tables', 'sos', 'complaints',
            'advertisements', 'banner_items', 'popular_destinations', 'gift_purchases',
            'gift_cards', 'on_boarding', 'cms_pages', 'dynamic_notifications',
            'email_templates', 'review_attributes', 'rental_packages', 'providers_workers',
            'providers_services', 'provider_categories', 'parcel_weights', 'parcel_categories',
            'brands', 'taxes', 'promos', 'providers_coupons', 'rental_coupons',
            'parcel_coupons', 'coupons', 'subscription_histories', 'subscription_plans',
            'vendor_attributes', 'vendor_products', 'vendor_categories', 'car_models',
            'car_makes', 'rental_vehicle_types', 'vehicle_types', 'services',
            'payouts', 'driver_payouts', 'wallet_transactions', 'wallet',
            'documents_verify', 'documents', 'zones',
            'rides', 'provider_orders', 'rental_orders', 'parcel_orders', 'vendor_orders',
            'vendors', 'app_users', 'currencies', 'settings', 'sections',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function dropLegacyStubTables(): void
    {
        $legacy = [
            'sections', 'settings', 'currencies', 'app_users', 'vendors',
            'vendor_orders', 'parcel_orders', 'rental_orders', 'provider_orders',
            'rides', 'documents', 'documents_verify', 'driver_payouts', 'services',
            'wallet', 'zones', 'car_makes', 'car_models', 'vehicle_types', 'rental_vehicle_types',
        ];

        Schema::disableForeignKeyConstraints();
        foreach ($legacy as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();
    }

    private function createOrderTable(string $tableName, array $extra = []): void
    {
        Schema::create($tableName, function (Blueprint $table) use ($extra) {
            $table->string('id', 64)->primary();
            $table->string('status', 64)->nullable()->index();
            $table->string('section_id', 64)->nullable()->index();
            $table->string('sectionId', 64)->nullable()->index();
            $table->string('authorID', 64)->nullable()->index();
            $table->decimal('subTotal', 15, 2)->nullable();
            $table->decimal('discount', 15, 2)->nullable();
            $table->decimal('tip_amount', 15, 2)->nullable();
            $table->decimal('adminCommission', 15, 2)->nullable();
            $table->string('adminCommissionType', 32)->nullable();
            $table->string('payment_method', 64)->nullable();
            $table->string('paymentMethod', 64)->nullable();
            $table->string('paymentStatus', 32)->nullable();
            $table->string('couponId', 64)->nullable();
            $table->string('couponCode', 64)->nullable();
            $table->json('taxSetting')->nullable();
            $table->json('author')->nullable();
            $table->json('driver')->nullable();
            $table->json('vendor')->nullable();
            $table->json('provider')->nullable();
            $table->json('products')->nullable();
            $table->json('address')->nullable();
            $table->json('receiver')->nullable();
            $table->json('sender')->nullable();
            $table->json('rejectedByDrivers')->nullable();
            $table->timestamp('createdAt')->nullable()->index();
            $table->json('payload')->nullable();
            $table->timestamps();

            foreach ($extra as $column => $type) {
                if ($type === true) {
                    $table->string($column, 64)->nullable()->index();
                } elseif ($type === 'boolean') {
                    $table->boolean($column)->default(false);
                } elseif ($type === 'timestamp') {
                    $table->timestamp($column)->nullable();
                } else {
                    $table->string($column, 64)->nullable();
                }
            }
        });
    }

    private function createCatalogTable(string $tableName, array $indexedStrings = []): void
    {
        Schema::create($tableName, function (Blueprint $table) use ($indexedStrings) {
            $table->string('id', 64)->primary();
            $table->string('title')->nullable();
            $table->string('name')->nullable();
            $table->boolean('isActive')->nullable();
            $table->boolean('isEnable')->nullable();
            $table->boolean('publish')->nullable();
            $table->boolean('isEnabled')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->timestamp('createdAt')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            foreach ($indexedStrings as $column => $type) {
                if ($type === true) {
                    $table->string($column, 64)->nullable()->index();
                } else {
                    $table->string($column, 128)->nullable()->index();
                }
            }
        });
    }

    private function createPivotTable(string $tableName, array $columns): void
    {
        Schema::create($tableName, function (Blueprint $table) use ($columns) {
            $table->string('id', 64)->primary();
            foreach ($columns as $column) {
                $table->string($column, 64)->nullable()->index();
            }
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }
};
