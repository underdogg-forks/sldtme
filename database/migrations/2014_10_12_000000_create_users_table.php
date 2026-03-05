<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->boolean('is_placeholder')->default(false);

            // Two-factor authentication columns
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();

            $table->uuid('current_team_id')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->string('timezone');
            $table->enum('week_start', [
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
                'saturday',
                'sunday',
            ]);
            $table->timestamps();

            // MariaDB doesn't support partial indexes with WHERE clauses
            // Use a regular unique index instead and enforce the constraint at application level
            $table->unique('email');

            // Foreign key for current_team_id
            $table->foreign('current_team_id', 'organizations_current_organization_id_foreign')
                ->references('id')
                ->on('organizations')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
