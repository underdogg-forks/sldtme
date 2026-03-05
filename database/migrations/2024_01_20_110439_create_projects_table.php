<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('projects', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('color', 16);
            $table->integer('billable_rate')->unsigned()->nullable();
            $table->boolean('is_public')->default(false);

            // Billable flag - set based on billable_rate during migration
            $table->boolean('is_billable')->nullable();

            // Archival
            $table->dateTime('archived_at')->nullable();

            // Time tracking
            $table->integer('estimated_time')->unsigned()->nullable();
            $table->bigInteger('spent_time')->unsigned()->default(0);

            $table->uuid('client_id')->nullable();
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->uuid('organization_id');
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
