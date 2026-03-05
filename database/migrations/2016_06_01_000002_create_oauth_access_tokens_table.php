<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('oauth_access_tokens', static function (Blueprint $table): void {
            $table->string('id', 100)->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->uuid('client_id');
            $table->string('name')->nullable();
            $table->text('scopes')->nullable();
            $table->boolean('revoked');

            // Reminder notifications
            $table->dateTime('reminder_sent_at')->nullable();
            $table->dateTime('expired_info_sent_at')->nullable();

            $table->timestamps();
            $table->dateTime('expires_at')->nullable();

            // Foreign key to users - can be added here (users already exist)
            $table->foreign('user_id', 'oauth_access_tokens_user_id_foreign')
                ->references('id')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            // Foreign key to oauth_clients added in separate migration
            // (oauth_clients created after this table)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oauth_access_tokens');
    }
};
