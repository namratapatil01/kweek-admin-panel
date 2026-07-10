<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phone_otps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('phone_number', 32);
            $table->string('country_code', 8)->nullable();
            $table->string('otp_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->string('role', 32)->default('driver');
            $table->timestamps();

            $table->index(['phone_number', 'country_code', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_otps');
    }
};
