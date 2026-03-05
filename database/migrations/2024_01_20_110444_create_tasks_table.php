<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tasks', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name', 500);

            // Task lifecycle
            $table->dateTime('done_at')->nullable();

            // Time tracking
            $table->integer('estimated_time')->unsigned()->nullable();
            $table->bigInteger('spent_time')->unsigned()->default(0);

            $table->uuid('project_id');
            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
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
        Schema::dropIfExists('tasks');
    }
};
