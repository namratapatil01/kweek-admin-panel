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
        if (! Schema::hasTable('app_users') || Schema::hasColumn('app_users', 'ownerId')) {
            return;
        }

        Schema::table('app_users', function (Blueprint $table) {
            if (Schema::hasColumn('app_users', 'isOwner')) {
                $table->string('ownerId', 36)->nullable()->after('isOwner');
            } else {
                $table->string('ownerId', 36)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_users', function (Blueprint $table) {
            $table->dropColumn('ownerId');
        });
    }
};
