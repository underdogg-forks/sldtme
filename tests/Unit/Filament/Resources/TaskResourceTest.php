<?php

namespace Tests\Unit\Filament\Resources;

use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Unit\Filament\FilamentTestCase;

#[UsesClass(TaskResource::class)]
class TaskResourceTest extends FilamentTestCase
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

    public function test_can_list_tasks(): void
    {
        // Arrange
        $tasks = Task::factory()->createMany(5);

        // Act
        $response = Livewire::test(Tasks\Pages\ListTasks::class);

        // Assert
        $response->assertSuccessful();
        $response->assertCanSeeTableRecords($tasks);
    }

    public function test_can_see_edit_page_of_task(): void
    {
        // Arrange
        $task = Task::factory()->create();

        // Act
        $response = Livewire::test(Tasks\Pages\EditTask::class, ['record' => $task->getKey()]);

        // Assert
        $response->assertSuccessful();
    }
}
