<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cashbacks')) {
            Schema::create('cashbacks', function (Blueprint $table) {
                $table->string('id', 64)->primary();
                $table->string('title')->nullable();
                $table->string('cashbackType', 32)->nullable();
                $table->decimal('cashbackAmount', 15, 2)->nullable();
                $table->decimal('cashbackValue', 15, 2)->nullable();
                $table->decimal('maximumDiscount', 15, 2)->nullable();
                $table->decimal('minumumPurchaseAmount', 15, 2)->nullable();
                $table->unsignedInteger('redeemLimit')->nullable();
                $table->boolean('allCustomer')->default(false);
                $table->boolean('allPayment')->default(false);
                $table->boolean('isEnabled')->default(true)->index();
                $table->timestamp('startDate')->nullable();
                $table->timestamp('endDate')->nullable();
                $table->json('customerIds')->nullable();
                $table->json('paymentMethods')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('cashback_redeems')) {
            Schema::create('cashback_redeems', function (Blueprint $table) {
                $table->string('id', 64)->primary();
                $table->string('cashbackId', 64)->index();
                $table->string('userId', 64)->index();
                $table->string('orderId', 64)->nullable()->index();
                $table->timestamp('createdAt')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cashback_redeems');
        Schema::dropIfExists('cashbacks');
    }
};
