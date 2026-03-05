<?php

namespace Tests\Unit\Filament\Resources;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
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

    public function test_can_list_projects(): void
    {
        // Arrange
        $projects = Project::factory()->createMany(5);

        // Act
        $response = Livewire::test(Projects\Pages\ListProjects::class);

        // Assert
        $response->assertSuccessful();
        $response->assertCanSeeTableRecords($projects);
    }

    public function test_can_see_edit_page_of_project(): void
    {
        // Arrange
        $project = Project::factory()->create();

        // Act
        $response = Livewire::test(Projects\Pages\EditProject::class, ['record' => $project->getKey()]);

        // Assert
        $response->assertSuccessful();
    }
}
