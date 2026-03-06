<?php

namespace Unit\Filament\Resources;

use App\Filament\Resources\OrganizationInvitations\OrganizationInvitationResource;
use App\Filament\Resources\OrganizationInvitations\Pages\ListOrganizationInvitations;
use App\Filament\Resources\OrganizationInvitations\Pages\EditOrganizationInvitation;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Unit\Filament\FilamentTestCase;

#[UsesClass(OrganizationInvitationResource::class)]
class OrganizationInvitationResourceTest extends FilamentTestCase
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
    public function it_can_list_organization_invitations(): void
    {
        /* Arrange */
        $user                    = User::factory()->create();
        $organization            = Organization::factory()->withOwner($user)->create();
        $organizationInvitations = OrganizationInvitation::factory()->forOrganization($organization)->createMany(5);

        /* Act */
        $response = Livewire::test(ListOrganizationInvitations::class);

        /* Assert */
        $response->assertSuccessful();
        $response->assertCanSeeTableRecords($organizationInvitations);
    }

    #[Test]
    public function it_can_see_edit_page_of_organization_invitation(): void
    {
        /* Arrange */
        $organization           = Organization::factory()->create();
        $organizationInvitation = OrganizationInvitation::factory()->forOrganization($organization)->create();

        /* Act */
        $response = Livewire::test(EditOrganizationInvitation::class, [
            'record' => $organizationInvitation->getKey(),
        ]);

        /* Assert */
        $response->assertSuccessful();
    }

    #[Test]
    public function it_can_delete_a_organization_invitation(): void
    {
        /* Arrange */
        $organization           = Organization::factory()->create();
        $organizationInvitation = OrganizationInvitation::factory()->forOrganization($organization)->create();

        /* Act */
        $response = Livewire::test(EditOrganizationInvitation::class, [
            'record' => $organizationInvitation->getKey(),
        ])->callAction(DeleteAction::class);

        /* Assert */
        $response->assertSuccessful();
        $this->assertDatabaseMissing(OrganizationInvitation::class, [
            'id' => $organizationInvitation->getKey(),
        ]);
    }

    #[Test]
    public function it_can_create_organization_invitation_through_modal(): void
    {
        /* Arrange */
        $organization = Organization::factory()->create();

        /* Act */
        $response = Livewire::test(EditOrganizationInvitation::class, [
            'record' => $organization->id,
        ])
            ->set('open', true)
            ->set('form.email', 'john.doe@example.com')
            ->set('form.name', 'John Doe')
            ->call('save');

        /* Assert */
        $response->assertSuccessful();
        $this->assertDatabaseHas(OrganizationInvitation::class, [
            'email' => 'john.doe@example.com',
            'name'  => 'John Doe',
        ]);
    }

    #[Test]
    public function it_can_edit_organization_invitation_through_modal(): void
    {
        /* Arrange */
        $organization           = Organization::factory()->create();
        $organizationInvitation = OrganizationInvitation::factory()->forOrganization($organization)->create();

        /* Act */
        $response = Livewire::test(EditOrganizationInvitation::class, [
            'record' => $organizationInvitation->getKey(),
        ])
            ->set('open', true)
            ->set('form.email', 'jane.doe@example.com')
            ->set('form.name', 'Jane Doe')
            ->call('save');

        /* Assert */
        $response->assertSuccessful();
        $this->assertDatabaseHas(OrganizationInvitation::class, [
            'id'    => $organizationInvitation->getKey(),
            'email' => 'jane.doe@example.com',
            'name'  => 'Jane Doe',
        ]);
    }

    #[Test]
    public function it_can_delete_organization_invitation_through_modal(): void
    {
        /* Arrange */
        $organization           = Organization::factory()->create();
        $organizationInvitation = OrganizationInvitation::factory()->forOrganization($organization)->create();

        /* Act */
        $response = Livewire::test(EditOrganizationInvitation::class, [
            'record' => $organizationInvitation->getKey(),
        ])
            ->set('open', true)
            ->call('delete');

        /* Assert */
        $response->assertSuccessful();
        $this->assertDatabaseMissing(OrganizationInvitation::class, [
            'id' => $organizationInvitation->getKey(),
        ]);
    }
}
