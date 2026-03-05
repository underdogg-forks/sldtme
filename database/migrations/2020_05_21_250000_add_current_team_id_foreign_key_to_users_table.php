<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * Add foreign key constraint for user_id in organizations table.
     * This is done as a separate migration after users table is created.
     */
    public function up(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->foreign('current_team_id', 'users_current_team_id_foreign')
                ->references('id')
                ->on('organizations')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->dropForeign('users_current_team_id_foreign');
        });
    }
};
