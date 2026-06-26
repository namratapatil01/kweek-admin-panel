<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('car_makes', function (Blueprint $table) {
            $table->boolean('isActive')->default(1)->after('name');
        });
        Schema::table('car_models', function (Blueprint $table) {
            $table->boolean('isActive')->default(1)->after('car_make_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_models', function (Blueprint $table) {
            $table->dropColumn('isActive');
        });
        Schema::table('car_makes', function (Blueprint $table) {
            $table->dropColumn('isActive');
        });
    }
};
