<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        // Data cleanup: Remove any invalid references before FK constraints are enforced
        // (FKs are already defined in table creation migrations)

        DB::table('oauth_access_tokens')
            ->whereNotNull('user_id')
            ->whereNotExists(function (Builder $query): void {
                $query->select('id')
                    ->from('users')
                    ->whereColumn('oauth_access_tokens.user_id', 'users.id');
            })
            ->delete();

        DB::table('oauth_access_tokens')
            ->whereNotExists(function (Builder $query): void {
                $query->select('id')
                    ->from('oauth_clients')
                    ->whereColumn('oauth_access_tokens.client_id', 'oauth_clients.id');
            })
            ->delete();

        DB::table('oauth_auth_codes')
            ->whereNotExists(function (Builder $query): void {
                $query->select('id')
                    ->from('users')
                    ->whereColumn('oauth_auth_codes.user_id', 'users.id');
            })
            ->delete();

        DB::table('oauth_auth_codes')
            ->whereNotExists(function (Builder $query): void {
                $query->select('id')
                    ->from('oauth_clients')
                    ->whereColumn('oauth_auth_codes.client_id', 'oauth_clients.id');
            })
            ->delete();

        DB::table('oauth_clients')
            ->whereNotNull('user_id')
            ->whereNotExists(function (Builder $query): void {
                $query->select('id')
                    ->from('users')
                    ->whereColumn('oauth_clients.user_id', 'users.id');
            })
            ->delete();
    }

    public function down(): void
    {
        // No-op: Data cleanup cannot be reversed
        // Foreign keys are managed by their respective table migrations
    }
};
