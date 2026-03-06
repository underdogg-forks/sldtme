<?php

namespace Unit\Filament\Resources;

use App\Filament\Resources\ProjectMembers\ProjectMemberResource;
use App\Filament\Resources\ProjectMembers\Pages\ListProjectMembers;
use App\Filament\Resources\ProjectMembers\Pages\EditProjectMember;
use App\Filament\Resources\ProjectMembers\Pages\CreateProjectMember;
use App\Models\ProjectMember;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Unit\Filament\FilamentTestCase;

#[UsesClass(ProjectMemberResource::class)]
class ProjectMemberResourceTest extends FilamentTestCase
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

    public function test_can_create_project_member_through_modal(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $payload = [
            'user_id' => $user->id,
            'project_id' => $project->id,
            'role' => 'member',
        ];
        $component = Livewire::test(ListProjectMembers::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();
        $component->assertHasNoFormErrors();
        $this->assertDatabaseHas('project_members', $payload);
    }

    public function test_can_edit_project_member_through_modal(): void
    {
        $projectMember = ProjectMember::factory()->create();
        $payload = [
            'role' => 'admin',
        ];
        $component = Livewire::test(ListProjectMembers::class)
            ->mountAction('edit', ['record' => $projectMember->id])
            ->fillForm($payload)
            ->callMountedAction();
        $component->assertHasNoFormErrors();
        $this->assertDatabaseHas('project_members', array_merge($payload, ['id' => $projectMember->id]));
    }

    public function test_can_delete_project_member_through_modal(): void
    {
        $projectMember = ProjectMember::factory()->create();
        $component = Livewire::test(ListProjectMembers::class)
            ->mountAction('delete', ['record' => $projectMember->id])
            ->callMountedAction();
        $component->assertHasNoActionErrors();
        $this->assertDatabaseMissing('project_members', ['id' => $projectMember->id]);
    }
}

