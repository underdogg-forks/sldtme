<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditsTable extends Migration
{
    public function up(): void
    {
        $connection = config('audit.drivers.database.connection', config('database.default'));
        $table      = config('audit.drivers.database.table', 'audits');

        Schema::connection($connection)->create($table, static function (Blueprint $table): void {
            $morphPrefix = config('audit.user.morph_prefix', 'user');

            $table->bigIncrements('id');
            $table->string($morphPrefix . '_type')->nullable();
            $table->uuid($morphPrefix . '_id')->nullable();
            $table->string('event');
            // MariaDB requires explicit UUID columns instead of uuidMorphs()
            $table->string('auditable_type');
            $table->uuid('auditable_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('url')->nullable();
            // MariaDB stores IP addresses as varchar instead of specialized ipAddress type
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 1023)->nullable();
            $table->string('tags')->nullable();
            $table->timestamps();

            $table->index([$morphPrefix . '_id', $morphPrefix . '_type']);
        });
    }

    public function down(): void
    {
        $connection = config('audit.drivers.database.connection', config('database.default'));
        $table      = config('audit.drivers.database.table', 'audits');

        Schema::connection($connection)->drop($table);
    }
}
