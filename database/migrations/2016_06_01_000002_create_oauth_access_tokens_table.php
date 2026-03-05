<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
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

            // Foreign keys with restrict on delete
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('client_id')
                ->references('id')
                ->on('oauth_clients')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_access_tokens');
    }
};
