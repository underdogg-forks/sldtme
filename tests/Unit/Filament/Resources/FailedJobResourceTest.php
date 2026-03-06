<?php

namespace Unit\Filament\Resources;

use App\Filament\Resources\FailedJobs\FailedJobResource;
use App\Filament\Resources\FailedJobs\Pages\ListFailedJobs;
use App\Models\FailedJob;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Unit\Filament\FilamentTestCase;

#[UsesClass(FailedJobResource::class)]
class FailedJobResourceTest extends FilamentTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('auth.super_admins', ['admin@example.com']);
        $user = User::factory()->withPersonalOrganization()->create([
            'email' => 'admin@example.com',
        ]);

        $this->actingAs($user);
    }

    public function test_can_list_failed_jobs(): void
    {
        /* Arrange */
        $failedJobs = FailedJob::factory()->createMany(5);

        /* Act */
        $response = Livewire::test(ListFailedJobs::class);

        /* Assert */
        $response->assertSuccessful();
        $response->assertCanSeeTableRecords($failedJobs);
    }

    public function test_can_see_view_page_of_failed_job(): void
    {
        // No ViewFailedJobs page exists, so this test is not applicable.
        $this->markTestSkipped('No ViewFailedJobs page exists for FailedJobResource.');
    }
}
