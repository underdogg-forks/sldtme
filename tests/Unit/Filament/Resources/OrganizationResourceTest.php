<?php

namespace Unit\Filament\Resources;

use App\Filament\Resources\Organizations\OrganizationResource;
use App\Filament\Resources\Organizations\Pages\EditOrganization;
use App\Filament\Resources\Organizations\Pages\ListOrganizations;
use App\Filament\Resources\Organizations\RelationManagers\InvitationsRelationManager;
use App\Filament\Resources\Organizations\RelationManagers\UsersRelationManager;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use App\Service\DeletionService;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Unit\Filament\FilamentTestCase;

#[UsesClass(OrganizationResource::class)]
class OrganizationResourceTest extends FilamentTestCase
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
    public function it_can_list_organizations(): void
    {
        /* Arrange */
        $user          = User::factory()->create();
        $organizations = Organization::factory()->state([
            'user_id' => $user->getKey(),
        ])->createMany(5);

        /* Act */
        $response = Livewire::test(ListOrganizations::class);

        /* Assert */
        $response->assertSuccessful();
        $response->assertCanSeeTableRecords($organizations);
    }

    #[Test]
    public function it_can_see_edit_page_of_organization(): void
    {
        /* Arrange */
        $organization = Organization::factory()->create();

        /* Act */
        $response = Livewire::test(EditOrganization::class, ['record' => $organization->getKey()]);

        /* Assert */
        $response->assertSuccessful();
    }

    #[Test]
    public function it_can_delete_a_organization(): void
    {
        /* Arrange */
        $user = $this->createUserWithPermission();
        $this->mock(DeletionService::class, static function (MockInterface $mock) use ($user): void {
            $mock->shouldReceive('deleteOrganization')
                ->withArgs(fn (Organization $organizationArg) => $organizationArg->is($user->organization))
                ->once();
        });

        /* Act */
        $response = Livewire::test(EditOrganization::class, ['record' => $user->organization->getKey()])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        /* Assert */
        $response->assertSuccessful();
    }

    #[Test]
    public function it_can_list_related_users(): void
    {
        /* Arrange */
        $organization = Organization::factory()->create();
        $user1        = User::factory()->create();
        $user2        = User::factory()->create();
        $organization->users()->attach($user1);
        $organization->users()->attach($user2);

        /* Act */
        $response = Livewire::test(UsersRelationManager::class, [
            'ownerRecord' => $organization,
            'pageClass'   => EditOrganization::class,
        ]);

        /* Assert */
        $response->assertSuccessful();
        $response->assertCanSeeTableRecords($organization->users()->get());
    }

    #[Test]
    public function it_can_list_related_invitations(): void
    {
        /* Arrange */
        $organization            = Organization::factory()->create();
        $organizationInvitations = OrganizationInvitation::factory()->forOrganization($organization)->createMany(5);

        /* Act */
        $response = Livewire::test(InvitationsRelationManager::class, [
            'ownerRecord' => $organization,
            'pageClass'   => EditOrganization::class,
        ]);

        /* Assert */
        $response->assertSuccessful();
        $response->assertCanSeeTableRecords($organizationInvitations);
    }

    #[Test]
    public function it_can_create_organization_through_modal(): void
    {
        /* Arrange */
        $user = $this->createUserWithPermission();

        /* Act */
        $response = Livewire::test(EditOrganization::class, ['record' => $user->organization->getKey()])
            ->set('openCreateModal', true)
            ->set('newOrganization.name', 'New Organization')
            ->call('saveNewOrganization')
            ->assertHasNoActionErrors();

        /* Assert */
        $response->assertSuccessful();
        $this->assertDatabaseHas('organizations', [
            'name'     => 'New Organization',
            'user_id'  => $user->id,
        ]);
    }

    #[Test]
    public function it_can_edit_organization_through_modal(): void
    {
        /* Arrange */
        $organization = Organization::factory()->create([
            'name' => 'Old Name',
        ]);

        /* Act */
        $response = Livewire::test(EditOrganization::class, ['record' => $organization->getKey()])
            ->set('openEditModal', true)
            ->set('editingOrganization.name', 'Updated Name')
            ->call('saveEditingOrganization')
            ->assertHasNoActionErrors();

        /* Assert */
        $response->assertSuccessful();
        $this->assertDatabaseHas('organizations', [
            'id'   => $organization->id,
            'name' => 'Updated Name',
        ]);
    }

    #[Test]
    public function it_can_delete_organization_through_modal(): void
    {
        /* Arrange */
        $user = $this->createUserWithPermission();
        $organization = Organization::factory()->create();

        /* Act */
        $response = Livewire::test(EditOrganization::class, ['record' => $organization->getKey()])
            ->set('openDeleteModal', true)
            ->call('deleteOrganization')
            ->assertHasNoActionErrors();

        /* Assert */
        $response->assertSuccessful();
        $this->assertDatabaseMissing('organizations', [
            'id' => $organization->id,
        ]);
    }
}
