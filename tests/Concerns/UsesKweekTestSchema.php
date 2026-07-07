<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait UsesKweekTestSchema
{
    protected function setUpKweekSchema(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('id', 128)->primary();
            $table->json('value');
            $table->timestamps();
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('name')->nullable();
            $table->string('serviceType', 64)->nullable();
            $table->string('serviceTypeFlag', 64)->nullable();
            $table->boolean('isActive')->default(true);
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

        Schema::create('app_users', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phoneNumber', 32)->nullable();
            $table->string('password')->nullable();
            $table->string('role', 32)->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('isActive')->default(true);
            $table->string('sectionId', 64)->nullable();
            $table->string('section_id', 64)->nullable();
            $table->string('fcmToken')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->unsignedBigInteger('role_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    protected function tearDownKweekSchema(): void
    {
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('app_users');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('users');
    }
}
