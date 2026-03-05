<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // Create organization_user table (renamed to 'members' in later migrations)
        // For fresh installs, create it directly as 'members'
        Schema::create('members', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role')->nullable();
            $table->integer('billable_rate')->unsigned()->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'user_id']);

            // Foreign keys - restrict on delete for referential integrity
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
