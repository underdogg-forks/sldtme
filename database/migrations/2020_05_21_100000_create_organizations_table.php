<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('organizations', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();
            $table->string('name');
            $table->boolean('personal_team');
            $table->integer('billable_rate')->unsigned()->nullable();
            $table->string('currency', 3);

            // Employee permissions and settings
            $table->boolean('employees_can_see_billable_rates')->default(false);
            $table->boolean('employees_can_manage_tasks')->default(false);

            // Time entry settings
            $table->boolean('prevent_overlapping_time_entries')->default(false);

            // Localization formats
            $table->string('number_format')->nullable();
            $table->string('currency_format')->nullable();
            $table->string('date_format')->nullable();
            $table->string('interval_format')->nullable();
            $table->string('time_format')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
