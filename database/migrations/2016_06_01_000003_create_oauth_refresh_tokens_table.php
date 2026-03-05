<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('oauth_refresh_tokens', static function (Blueprint $table): void {
            $table->string('id', 100)->primary();
            $table->string('access_token_id', 100)->index();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();

            // Foreign key with restrict on delete
            $table->foreign('access_token_id')
                ->references('id')
                ->on('oauth_access_tokens')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oauth_refresh_tokens');
    }
};
