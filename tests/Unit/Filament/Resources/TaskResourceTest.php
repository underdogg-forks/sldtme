<?php

namespace Unit\Filament\Resources;

use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Unit\Filament\FilamentTestCase;
use App\Filament\Resources\Tasks\Pages\ListTasks;
use App\Filament\Resources\Tasks\Pages\EditTask;
use App\Filament\Resources\Tasks\Pages\CreateTask;

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
        /* Arrange */
        $tasks = Task::factory()->createMany(5);

        /* Act */
        $response = Livewire::test(ListTasks::class);

        /* Assert */
        $response->assertSuccessful();
        $response->assertCanSeeTableRecords($tasks);
    }

    public function test_can_see_edit_page_of_task(): void
    {
        /* Arrange */
        $task = Task::factory()->create();

        /* Act */
        $response = Livewire::test(EditTask::class, ['record' => $task->getKey()]);

        /* Assert */
        $response->assertSuccessful();
    }

    public function test_can_create_task(): void
    {
        /* Arrange */
        $user = $this->createUserWithPermission();
        $payload = [
            'name' => 'Test Task',
            'organization_id' => $user->organization->id,
        ];

        /* Act */
        $response = Livewire::test(CreateTask::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $response->assertHasNoFormErrors();
        $response->assertSuccessful();
        $this->assertDatabaseHas('tasks', $payload);
    }

    public function test_cannot_create_task_without_required_fields(): void
    {
        /* Act */
        $response = Livewire::test(CreateTask::class)
            ->fillForm([])
            ->call('create');

        /* Assert */
        $response->assertHasFormErrors(['name', 'organization_id']);
    }

    public function test_can_edit_task(): void
    {
        /* Arrange */
        $task = Task::factory()->create();
        $payload = [
            'name' => 'Updated Task',
            'organization_id' => $task->organization_id,
        ];

        /* Act */
        $response = Livewire::test(EditTask::class, ['record' => $task->getKey()])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $response->assertHasNoFormErrors();
        $response->assertSuccessful();
        $this->assertDatabaseHas('tasks', array_merge($payload, ['id' => $task->id]));
    }

    public function test_cannot_edit_task_with_invalid_data(): void
    {
        /* Arrange */
        $task = Task::factory()->create();
        $payload = [
            'name' => '',
            'organization_id' => null,
        ];

        /* Act */
        $response = Livewire::test(EditTask::class, ['record' => $task->getKey()])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $response->assertHasFormErrors(['name', 'organization_id']);
    }

    public function test_can_delete_task(): void
    {
        /* Arrange */
        $task = Task::factory()->create();

        /* Act */
        $response = Livewire::test(EditTask::class, ['record' => $task->getKey()])
            ->callAction('delete');

        /* Assert */
        $response->assertHasNoActionErrors();
        $response->assertSuccessful();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_table_filters_and_sorting(): void
    {
        /* Arrange */
        $user = $this->createUserWithPermission();
        $orgA = $user->organization;
        $orgB = \App\Models\Organization::factory()->create();
        $taskA = Task::factory()->for($orgA)->create(['name' => 'Alpha']);
        $taskB = Task::factory()->for($orgB)->create(['name' => 'Beta']);

        /* Act & Assert */
        $response = Livewire::test(ListTasks::class)
            ->filterTable('organization', $orgA->id)
            ->assertCanSeeTableRecords([$taskA])
            ->assertCanNotSeeTableRecords([$taskB]);

        Livewire::test(ListTasks::class)
            ->sortTable('name', 'desc')
            ->assertSuccessful();
    }

    public function test_bulk_delete_tasks(): void
    {
        /* Arrange */
        $tasks = Task::factory(3)->create();
        $ids = $tasks->pluck('id')->toArray();

        /* Act */
        $response = Livewire::test(ListTasks::class)
            ->callAction('delete', $ids);

        /* Assert */
        $response->assertSuccessful();
        foreach ($ids as $id) {
            $this->assertDatabaseMissing('tasks', ['id' => $id]);
        }
    }

    public function test_can_create_task_through_modal(): void
    {
        $user = $this->createUserWithPermission();
        $payload = [
            'name' => 'Modal Task',
            'organization_id' => $user->organization->id,
        ];
        $component = Livewire::test(ListTasks::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();
        $component->assertHasNoFormErrors();
        $this->assertDatabaseHas('tasks', $payload);
    }

    public function test_can_edit_task_through_modal(): void
    {
        $task = Task::factory()->create();
        $payload = [
            'name' => 'Edited Modal Task',
            'organization_id' => $task->organization_id,
        ];
        $component = Livewire::test(ListTasks::class)
            ->mountAction('edit', ['record' => $task->id])
            ->fillForm($payload)
            ->callMountedAction();
        $component->assertHasNoFormErrors();
        $this->assertDatabaseHas('tasks', array_merge($payload, ['id' => $task->id]));
    }

    public function test_can_delete_task_through_modal(): void
    {
        $task = Task::factory()->create();
        $component = Livewire::test(ListTasks::class)
            ->mountAction('delete', ['record' => $task->id])
            ->callMountedAction();
        $component->assertHasNoActionErrors();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
