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
        if (Schema::hasTable('car_makes') && ! Schema::hasColumn('car_makes', 'isActive')) {
            Schema::table('car_makes', function (Blueprint $table) {
                $table->boolean('isActive')->default(1)->after('name');
            });
        }

        if (Schema::hasTable('car_models') && ! Schema::hasColumn('car_models', 'isActive')) {
            Schema::table('car_models', function (Blueprint $table) {
                $table->boolean('isActive')->default(1)->after('car_make_id');
            });
        }
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
