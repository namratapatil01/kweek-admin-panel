<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverFormTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->boolean('publish')->default(true);
            $table->timestamps();
        });

        Schema::create('car_makes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('car_models', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('car_make_id')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicle_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('sectionId')->nullable();
            $table->timestamps();
        });

        Schema::create('rental_vehicle_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('sectionId')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rental_vehicle_types');
        Schema::dropIfExists('vehicle_types');
        Schema::dropIfExists('car_models');
        Schema::dropIfExists('car_makes');
        Schema::dropIfExists('zones');
    }
}
