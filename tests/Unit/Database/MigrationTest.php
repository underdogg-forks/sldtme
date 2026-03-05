<?php

namespace Tests\Unit\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_migration_and_rollback_runs_successfully(): void
    {
        $this->artisan('migrate:rollback')
            ->assertSuccessful();
    }
}
