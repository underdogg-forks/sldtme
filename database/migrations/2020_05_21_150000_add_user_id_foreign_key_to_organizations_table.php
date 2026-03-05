<?php

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
        Schema::table('organizations', static function (Blueprint $table): void {
            $table->foreign('user_id', 'organizations_user_id_foreign')
                ->references('id')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('organizations', static function (Blueprint $table): void {
            $table->dropForeign('organizations_user_id_foreign');
        });
    }
};
