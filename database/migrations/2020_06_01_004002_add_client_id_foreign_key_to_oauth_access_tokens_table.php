<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * Add foreign key constraint for client_id in oauth_access_tokens table.
     * This is done as a separate migration after oauth_clients table is created.
     */
    public function up(): void
    {
        Schema::table('oauth_access_tokens', static function (Blueprint $table): void {
            $table->foreign('client_id')
                ->references('id')
                ->on('oauth_clients')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('oauth_access_tokens', static function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });
    }
};
