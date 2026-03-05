<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('project_members', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->integer('billable_rate')->unsigned()->nullable();
            $table->uuid('project_id');
            $table->uuid('member_id');
            $table->timestamps();

            $table->unique(['project_id', 'member_id']);

            // Foreign keys with restrict on delete
            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('member_id')
                ->references('id')
                ->on('members')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_members');
    }
};
