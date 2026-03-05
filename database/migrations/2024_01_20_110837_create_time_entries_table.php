<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('time_entries', static function (Blueprint $table): void {
            $table->id();
            // Extended description to 5000 characters
            $table->string('description', 5000);
            $table->dateTime('start');
            $table->dateTime('end')->nullable();
            $table->integer('billable_rate')->unsigned()->nullable();
            $table->boolean('billable')->default(false);

            // Import tracking
            $table->boolean('is_imported')->default(false);

            // Reminder notifications
            $table->dateTime('still_active_email_sent_at')->nullable();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();

            // MariaDB uses json() instead of jsonb()
            $table->json('tags')->nullable();
            $table->timestamps();

            // Foreign keys with restrict on delete for referential integrity
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreign('member_id')
                ->references('id')
                ->on('members')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreign('task_id')
                ->references('id')
                ->on('tasks')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index('start');
            $table->index('end');
            $table->index('billable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
