<?php

namespace Tests\Unit\Filament\Resources;

use App\Exceptions\Api\CanNotDeleteUserWhoIsOwnerOfOrganizationWithMultipleMembers;
use App\Filament\Resources\TimeEntries\TimeEntryResource;
use App\Models\Organization;
use App\Models\User;
use App\Service\DeletionService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Unit\Filament\FilamentTestCase;

#[UsesClass(TimeEntryResource::class)]
class UserResourceTest extends FilamentTestCase
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

    public function test_can_list_users(): void
    {
        // Arrange
        $users = User::factory()->createMany(5);

        // Act
        $response = Livewire::test(Users\Pages\ListUsers::class);

        // Assert
        $response->assertSuccessful();
        $response->assertCanSeeTableRecords($users);
    }

    public function test_can_see_edit_page_of_user(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = Livewire::test(Users\Pages\EditUser::class, ['record' => $user->getKey()]);

        // Assert
        $response->assertSuccessful();
    }

    public function test_can_see_view_page_of_user(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = Livewire::test(Users\Pages\ViewUser::class, ['record' => $user->getKey()]);

        // Assert
        $response->assertSuccessful();
    }

    public function test_can_see_create_page_of_user(): void
    {
        // Act
        $response = Livewire::test(Users\Pages\CreateUser::class);

        // Assert
        $response->assertSuccessful();
    }

    public function test_can_create_user(): void
    {
        // Arrange
        $userFake = User::factory()->make();

        // Act
        $response = Livewire::test(Users\Pages\CreateUser::class)
            ->fillForm([
                'name'            => $userFake->name,
                'email'           => $userFake->email,
                'password_create' => 'password',
                'timezone'        => $userFake->timezone,
                'week_start'      => $userFake->week_start->value,
                'currency'        => 'EUR',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Assert
        $response->assertSuccessful();
        $user = User::where('email', $userFake->email)->first();
        $this->assertNotNull($user);
        $this->assertSame($userFake->name, $user->name);
        $this->assertSame($userFake->email, $user->email);
        $this->assertSame($userFake->timezone, $user->timezone);
        $this->assertSame($userFake->week_start->value, $user->week_start->value);
        $organization = $user->ownedTeams()->first();
        $this->assertNotNull($organization);
        $this->assertSame('EUR', $organization->currency);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_can_delete_a_user(): void
    {
        // Arrange
        $user = $this->createUserWithPermission();
        $this->mock(DeletionService::class, static function (MockInterface $mock) use ($user): void {
            $mock->shouldReceive('deleteUser')
                ->withArgs(fn (User $userArg) => $userArg->is($user->user))
                ->once();
        });

        // Act
        $response = Livewire::test(Users\Pages\EditUser::class, ['record' => $user->user->getKey()])
            ->callAction('delete');

        // Assert
        $response->assertHasNoActionErrors();
        $response->assertSuccessful();
    }

    public function test_delete_user_shows_error_notification_on_failure(): void
    {
        // Arrange
        $user = $this->createUserWithPermission();
        $this->mock(DeletionService::class, static function (MockInterface $mock) use ($user): void {
            $mock->shouldReceive('deleteUser')
                ->withArgs(fn (User $userArg) => $userArg->is($user->user))
                ->andThrow(new CanNotDeleteUserWhoIsOwnerOfOrganizationWithMultipleMembers());
        });

        // Act
        $response = Livewire::test(Users\Pages\EditUser::class, ['record' => $user->user->getKey()])
            ->callAction('delete');

        // Assert
        $response->assertNotified(__('exceptions.api.can_not_delete_user_who_is_owner_of_organization_with_multiple_members'));
        $response->assertSuccessful();
    }

    public function test_can_list_related_organizations(): void
    {
        // Arrange
        $user              = User::factory()->create();
        $ownedOrganization = Organization::factory()->withOwner($user)->create();
        $organization      = Organization::factory()->create();

        // Act
        $response = Livewire::test(Users\RelationManagers\OrganizationsRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => Users\Pages\EditUser::class,
        ]);

        // Assert
        $response->assertSuccessful();
        $response->assertCanSeeTableRecords($user->organizations()->get());
        $response->assertCanNotSeeTableRecords($user->ownedTeams()->get());
    }

    public function test_can_list_related_owned_organizations(): void
    {
        // Arrange
        $user              = User::factory()->create();
        $ownedOrganization = Organization::factory()->withOwner($user)->create();
        $organization      = Organization::factory()->create();

        // Act
        $response = Livewire::test(Users\RelationManagers\OwnedOrganizationsRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => Users\Pages\EditUser::class,
        ]);

        // Assert
        $response->assertSuccessful();
        $response->assertCanSeeTableRecords($user->ownedTeams()->get());
        $response->assertCanNotSeeTableRecords($user->organizations()->get());
    }
}
