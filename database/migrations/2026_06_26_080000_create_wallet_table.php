<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('wallet')) {
            return;
        }

        Schema::create('wallet', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('user_id', 50)->nullable();
            $table->double('amount', 15, 2)->default(0.00);
            $table->text('note')->nullable();
            $table->boolean('isTopUp')->default(true);
            $table->string('payment_method', 100)->nullable();
            $table->string('payment_status', 50)->default('success');
            $table->string('transactionUser', 50)->nullable();
            $table->string('order_id', 50)->nullable();
            $table->timestamp('date')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallet');
    }
}
