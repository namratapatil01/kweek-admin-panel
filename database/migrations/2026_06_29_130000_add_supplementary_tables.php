<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vendor_filters')) {
            Schema::create('vendor_filters', function (Blueprint $table) {
                $table->string('id', 64)->primary();
                $table->string('name')->nullable()->index();
                $table->json('options')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('arropay_disbursement_withdrawals')) {
            Schema::create('arropay_disbursement_withdrawals', function (Blueprint $table) {
                $table->string('id', 64)->primary();
                $table->string('transaction_id', 64)->nullable()->index();
                $table->string('order_number', 128)->nullable()->index();
                $table->string('channel', 32)->nullable()->index();
                $table->string('full_name')->nullable();
                $table->string('phone', 32)->nullable();
                $table->string('account_number', 64)->nullable();
                $table->string('bank_code', 32)->nullable();
                $table->decimal('amount', 15, 2)->default(0);
                $table->string('notify_url')->nullable();
                $table->string('status', 32)->default('PENDING')->index();
                $table->string('otp_hash')->nullable();
                $table->string('provider_order_number', 64)->nullable();
                $table->string('gateway', 64)->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->json('payload')->nullable();
            });
        }

        if (! Schema::hasTable('arropay_v2_payments')) {
            Schema::create('arropay_v2_payments', function (Blueprint $table) {
                $table->string('id', 128)->primary();
                $table->string('payment_id', 128)->nullable()->index();
                $table->string('refno', 128)->nullable()->index();
                $table->decimal('amount', 15, 2)->nullable();
                $table->string('email')->nullable();
                $table->string('firstname')->nullable();
                $table->string('lastname')->nullable();
                $table->string('redirect_url')->nullable();
                $table->string('flow', 64)->nullable();
                $table->json('request_payload')->nullable();
                $table->json('response_data')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('chat_threads')) {
            Schema::create('chat_threads', function (Blueprint $table) {
                $table->string('id', 64)->primary();
                $table->string('chat_id', 64)->index();
                $table->string('chat_type', 32)->index();
                $table->text('message')->nullable();
                $table->string('messageType', 32)->nullable();
                $table->string('senderId', 64)->nullable()->index();
                $table->string('receiverId', 64)->nullable()->index();
                $table->string('orderId', 64)->nullable()->index();
                $table->string('url')->nullable();
                $table->string('videoThumbnail')->nullable();
                $table->timestamp('createdAt')->nullable()->index();
                $table->json('payload')->nullable();
                $table->timestamps();

                $table->index(['chat_type', 'chat_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('arropay_v2_payments');
        Schema::dropIfExists('arropay_disbursement_withdrawals');
        Schema::dropIfExists('chat_threads');
        Schema::dropIfExists('vendor_filters');
    }
};
