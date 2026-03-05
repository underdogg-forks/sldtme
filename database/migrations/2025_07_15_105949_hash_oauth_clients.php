<?php

use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        // This could be optimized to run all the updates in the eachById
        DB::table('oauth_clients')->whereNotNull('secret')->eachById(function ($client): void {
            $secret = $client->secret;
            if (Hash::isHashed($secret) && ! Hash::needsRehash($secret)) {
                return; // Already hashed and not needing rehash
            }
            DB::table('oauth_clients')
                ->where('id', $client->id)
                ->update([
                    'secret' => Hash::make($secret),
                ]);
        });
    }

    public function down(): void
    {
        // This can not be reversed without a backup of the original secrets, for security reasons.
    }
};
