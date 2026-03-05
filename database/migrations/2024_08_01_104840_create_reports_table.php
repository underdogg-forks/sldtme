<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('reports', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false)->index();
            $table->string('share_secret', 40)->nullable()->index()->unique();
            // MariaDB uses json() instead of jsonb()
            $table->json('properties');
            $table->dateTime('public_until')->nullable();
            $table->uuid('organization_id');
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
