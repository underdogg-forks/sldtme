<?php

namespace Unit\Filament\Resources;

use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Unit\Filament\FilamentTestCase;

#[UsesClass(ProjectResource::class)]
class ProjectResourceTest extends FilamentTestCase
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
    public function it_can_list_projects(): void
    {
        /* Arrange */
        $projects = Project::factory()->createMany(5);

        /* Act */
        $response = Livewire::test(ListProjects::class);

        /* Assert */
        $response->assertSuccessful();
        $response->assertCanSeeTableRecords($projects);
    }

    #[Test]
    public function it_can_see_edit_page_of_project(): void
    {
        /* Arrange */
        $project = Project::factory()->create();

        /* Act */
        $response = Livewire::test(EditProject::class, ['record' => $project->getKey()]);

        /* Assert */
        $response->assertSuccessful();
    }

    #[Test]
    public function it_can_create_project(): void
    {
        /* Arrange */
        $user = $this->createUserWithPermission();
        $payload = [
            'name' => 'Test Project',
            'organization_id' => $user->organization->id,
        ];

        /* Act */
        $response = Livewire::test(CreateProject::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $response->assertHasNoFormErrors();
        $response->assertSuccessful();
        $this->assertDatabaseHas('projects', $payload);
    }

    #[Test]
    public function it_cannot_create_project_without_required_fields(): void
    {
        /* Act */
        $response = Livewire::test(CreateProject::class)
            ->fillForm([])
            ->call('create');

        /* Assert */
        $response->assertHasFormErrors(['name', 'organization_id']);
    }

    #[Test]
    public function it_can_edit_project(): void
    {
        /* Arrange */
        $project = Project::factory()->create();
        $payload = [
            'name' => 'Updated Project',
            'organization_id' => $project->organization_id,
        ];

        /* Act */
        $response = Livewire::test(EditProject::class, ['record' => $project->getKey()])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $response->assertHasNoFormErrors();
        $response->assertSuccessful();
        $this->assertDatabaseHas('projects', array_merge($payload, ['id' => $project->id]));
    }

    #[Test]
    public function it_cannot_edit_project_with_invalid_data(): void
    {
        /* Arrange */
        $project = Project::factory()->create();
        $payload = [
            'name' => '',
            'organization_id' => null,
        ];

        /* Act */
        $response = Livewire::test(EditProject::class, ['record' => $project->getKey()])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $response->assertHasFormErrors(['name', 'organization_id']);
    }

    #[Test]
    public function it_can_delete_project(): void
    {
        /* Arrange */
        $project = Project::factory()->create();

        /* Act */
        $response = Livewire::test(EditProject::class, ['record' => $project->getKey()])
            ->callAction('delete');

        /* Assert */
        $response->assertHasNoActionErrors();
        $response->assertSuccessful();
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    #[Test]
    public function it_table_filters_and_sorting(): void
    {
        /* Arrange */
        $user = $this->createUserWithPermission();
        $orgA = $user->organization;
        $orgB = \App\Models\Organization::factory()->create();
        $projectA = Project::factory()->for($orgA)->create(['name' => 'Alpha']);
        $projectB = Project::factory()->for($orgB)->create(['name' => 'Beta']);

        /* Act & Assert */
        $response = Livewire::test(ListProjects::class)
            ->filterTable('organization', $orgA->id)
            ->assertCanSeeTableRecords([$projectA])
            ->assertCanNotSeeTableRecords([$projectB]);

        Livewire::test(ListProjects::class)
            ->sortTable('name', 'desc')
            ->assertSuccessful();
    }

    #[Test]
    public function it_bulk_deletes_projects(): void
    {
        /* Arrange */
        $projects = Project::factory(3)->create();
        $ids = $projects->pluck('id')->toArray();

        /* Act */
        $response = Livewire::test(ListProjects::class)
            ->callAction('delete', $ids);

        /* Assert */
        $response->assertSuccessful();
        foreach ($ids as $id) {
            $this->assertDatabaseMissing('projects', ['id' => $id]);
        }
    }

    #[Test]
    public function it_can_create_project_through_modal(): void
    {
        $user = $this->createUserWithPermission();
        $payload = [
            'name' => 'Modal Project',
            'organization_id' => $user->organization->id,
        ];
        $component = Livewire::test(ListProjects::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();
        $component->assertHasNoFormErrors();
        $this->assertDatabaseHas('projects', $payload);
    }

    #[Test]
    public function it_can_edit_project_through_modal(): void
    {
        $project = Project::factory()->create();
        $payload = [
            'name' => 'Edited Modal Project',
            'organization_id' => $project->organization_id,
        ];
        $component = Livewire::test(ListProjects::class)
            ->mountAction('edit', ['record' => $project->id])
            ->fillForm($payload)
            ->callMountedAction();
        $component->assertHasNoFormErrors();
        $this->assertDatabaseHas('projects', array_merge($payload, ['id' => $project->id]));
    }

    #[Test]
    public function it_can_delete_project_through_modal(): void
    {
        $project = Project::factory()->create();
        $component = Livewire::test(ListProjects::class)
            ->mountAction('delete', ['record' => $project->id])
            ->callMountedAction();
        $component->assertHasNoActionErrors();
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}
