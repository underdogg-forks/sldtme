<?php

namespace Unit\Filament\Resources;

use App\Filament\Resources\Audits\AuditResource;
use App\Filament\Resources\Audits\Pages\CreateAudit;
use App\Filament\Resources\Audits\Pages\EditAudit;
use App\Filament\Resources\Audits\Pages\ListAudits;
use App\Models\Audit;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Unit\Filament\FilamentTestCase;

#[UsesClass(AuditResource::class)]
class AuditResourceTest extends FilamentTestCase
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

    public function test_can_list_audits(): void
    {
        /* Arrange */
        $user      = $this->createUserWithPermission();
        $audits = Audit::factory()->count(5)->create();

        /* Act */
        $response = Livewire::test(ListAudits::class);

        /* Assert */
        $response->assertSuccessful();
        $response->assertCanSeeTableRecords($audits);
    }

    public function test_can_see_view_page_of_audit(): void
    {
        // No ViewAudit page exists, so this test is not applicable.
        $this->markTestSkipped('No ViewAudit page exists for AuditResource.');
    }

    public function test_can_create_audit(): void
    {
        /* Arrange */
        $user = $this->createUserWithPermission();
        $payload = [
            'event' => 'created',
            'auditable_type' => 'App\\Models\\TimeEntry',
            'auditable_id' => 1,
            'user_id' => $user->user->id,
            'old_values' => [],
            'new_values' => ['foo' => 'bar'],
        ];

        /* Act */
        $response = Livewire::test(CreateAudit::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $response->assertHasNoFormErrors();
        $response->assertSuccessful();
        $this->assertDatabaseHas('audits', [
            'event' => 'created',
            'user_id' => $user->user->id,
        ]);
    }

    public function test_cannot_create_audit_without_required_fields(): void
    {
        /* Act */
        $response = Livewire::test(CreateAudit::class)
            ->fillForm([])
            ->call('create');

        /* Assert */
        $response->assertHasFormErrors(['event', 'auditable_type', 'auditable_id', 'user_id']);
    }

    public function test_can_edit_audit(): void
    {
        /* Arrange */
        $audit = Audit::factory()->create();
        $payload = [
            'event' => 'updated',
            'auditable_type' => $audit->auditable_type,
            'auditable_id' => $audit->auditable_id,
            'user_id' => $audit->user_id,
            'old_values' => $audit->old_values,
            'new_values' => ['foo' => 'baz'],
        ];

        /* Act */
        $response = Livewire::test(EditAudit::class, ['record' => $audit->getKey()])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $response->assertHasNoFormErrors();
        $response->assertSuccessful();
        $this->assertDatabaseHas('audits', array_merge($payload, ['id' => $audit->id]));
    }

    public function test_cannot_edit_audit_with_invalid_data(): void
    {
        /* Arrange */
        $audit = Audit::factory()->create();
        $payload = [
            'event' => '',
            'auditable_type' => '',
            'auditable_id' => null,
            'user_id' => null,
            'old_values' => [],
            'new_values' => [],
        ];

        /* Act */
        $response = Livewire::test(EditAudit::class, ['record' => $audit->getKey()])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $response->assertHasFormErrors(['event', 'auditable_type', 'auditable_id', 'user_id']);
    }

    public function test_can_delete_audit(): void
    {
        /* Arrange */
        $audit = Audit::factory()->create();

        /* Act */
        $response = Livewire::test(EditAudit::class, ['record' => $audit->getKey()])
            ->callAction('delete');

        /* Assert */
        $response->assertHasNoActionErrors();
        $response->assertSuccessful();
        $this->assertDatabaseMissing('audits', ['id' => $audit->id]);
    }

    public function test_can_create_audit_through_modal(): void
    {
        $user = $this->createUserWithPermission();
        $payload = [
            'event' => 'modal_created',
            'auditable_type' => 'App\\Models\\TimeEntry',
            'auditable_id' => 2,
            'user_id' => $user->user->id,
            'old_values' => [],
            'new_values' => ['foo' => 'bar'],
        ];
        $component = Livewire::test(ListAudits::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();
        $component->assertHasNoFormErrors();
        $this->assertDatabaseHas('audits', [
            'event' => 'modal_created',
            'user_id' => $user->user->id,
        ]);
    }

    public function test_can_edit_audit_through_modal(): void
    {
        $audit = Audit::factory()->create();
        $payload = [
            'event' => 'modal_updated',
            'auditable_type' => $audit->auditable_type,
            'auditable_id' => $audit->auditable_id,
            'user_id' => $audit->user_id,
            'old_values' => $audit->old_values,
            'new_values' => ['foo' => 'modal_baz'],
        ];
        $component = Livewire::test(ListAudits::class)
            ->mountAction('edit', ['record' => $audit->id])
            ->fillForm($payload)
            ->callMountedAction();
        $component->assertHasNoFormErrors();
        $this->assertDatabaseHas('audits', array_merge($payload, ['id' => $audit->id]));
    }

    public function test_can_delete_audit_through_modal(): void
    {
        $audit = Audit::factory()->create();
        $component = Livewire::test(ListAudits::class)
            ->mountAction('delete', ['record' => $audit->id])
            ->callMountedAction();
        $component->assertHasNoActionErrors();
        $this->assertDatabaseMissing('audits', ['id' => $audit->id]);
    }
}
