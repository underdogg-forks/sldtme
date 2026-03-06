<?php

namespace Unit\Filament\Resources;

use App\Filament\Resources\Reports\ReportResource;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Unit\Filament\FilamentTestCase;
use App\Filament\Resources\Reports\Pages\ListReports;
use App\Filament\Resources\Reports\Pages\EditReport;
use App\Filament\Resources\Reports\Pages\CreateReport;

#[UsesClass(ReportResource::class)]
class ReportResourceTest extends FilamentTestCase
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

    #[Test]
    public function it_can_list_reports(): void
    {
        /* Arrange */
        $reports = Report::factory()->createMany(5);

        /* Act */
        $response = Livewire::test(ListReports::class);

        /* Assert */
        $response->assertSuccessful();
        $response->assertCanSeeTableRecords($reports);
    }

    #[Test]
    public function it_can_see_edit_page_of_report(): void
    {
        /* Arrange */
        $report = Report::factory()->create();

        /* Act */
        $response = Livewire::test(EditReport::class, ['record' => $report->getKey()]);

        /* Assert */
        $response->assertSuccessful();
    }

    #[Test]
    public function it_can_create_report(): void
    {
        /* Arrange */
        $user = $this->createUserWithPermission();
        $payload = [
            'name' => 'Test Report',
            'organization_id' => $user->organization->id,
        ];

        /* Act */
        $response = Livewire::test(CreateReport::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $response->assertHasNoFormErrors();
        $response->assertSuccessful();
        $this->assertDatabaseHas('reports', $payload);
    }

    #[Test]
    public function it_cannot_create_report_without_required_fields(): void
    {
        /* Act */
        $response = Livewire::test(CreateReport::class)
            ->fillForm([])
            ->call('create');

        /* Assert */
        $response->assertHasFormErrors(['name', 'organization_id']);
    }

    #[Test]
    public function it_can_edit_report(): void
    {
        /* Arrange */
        $report = Report::factory()->create();
        $payload = [
            'name' => 'Updated Report',
            'organization_id' => $report->organization_id,
        ];

        /* Act */
        $response = Livewire::test(EditReport::class, ['record' => $report->getKey()])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $response->assertHasNoFormErrors();
        $response->assertSuccessful();
        $this->assertDatabaseHas('reports', array_merge($payload, ['id' => $report->id]));
    }

    #[Test]
    public function it_cannot_edit_report_with_invalid_data(): void
    {
        /* Arrange */
        $report = Report::factory()->create();
        $payload = [
            'name' => '',
            'organization_id' => null,
        ];

        /* Act */
        $response = Livewire::test(EditReport::class, ['record' => $report->getKey()])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $response->assertHasFormErrors(['name', 'organization_id']);
    }

    #[Test]
    public function it_can_delete_report(): void
    {
        /* Arrange */
        $report = Report::factory()->create();

        /* Act */
        $response = Livewire::test(EditReport::class, ['record' => $report->getKey()])
            ->callAction('delete');

        /* Assert */
        $response->assertHasNoActionErrors();
        $response->assertSuccessful();
        $this->assertDatabaseMissing('reports', ['id' => $report->id]);
    }

    #[Test]
    public function it_can_create_report_through_modal(): void
    {
        /* Arrange */
        $user = $this->createUserWithPermission();
        $payload = [
            'name' => 'Test Report',
            'organization_id' => $user->organization->id,
        ];

        /* Act */
        $response = Livewire::test(CreateReport::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $response->assertHasNoFormErrors();
        $response->assertSuccessful();
        $this->assertDatabaseHas('reports', $payload);
    }

    #[Test]
    public function it_can_edit_report_through_modal(): void
    {
        /* Arrange */
        $report = Report::factory()->create();
        $payload = [
            'name' => 'Updated Report',
            'organization_id' => $report->organization_id,
        ];

        /* Act */
        $response = Livewire::test(EditReport::class, ['record' => $report->getKey()])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $response->assertHasNoFormErrors();
        $response->assertSuccessful();
        $this->assertDatabaseHas('reports', array_merge($payload, ['id' => $report->id]));
    }

    #[Test]
    public function it_can_delete_report_through_modal(): void
    {
        /* Arrange */
        $report = Report::factory()->create();

        /* Act */
        $response = Livewire::test(EditReport::class, ['record' => $report->getKey()])
            ->callAction('delete');

        /* Assert */
        $response->assertHasNoActionErrors();
        $response->assertSuccessful();
        $this->assertDatabaseMissing('reports', ['id' => $report->id]);
    }
}
