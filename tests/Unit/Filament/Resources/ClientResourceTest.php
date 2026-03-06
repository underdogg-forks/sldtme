<?php

namespace Unit\Filament\Resources;

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Clients\Pages\ListClients;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use App\Filament\Resources\Clients;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Unit\Filament\FilamentTestCase;
use App\Filament\Resources\Clients\Pages\EditClient;
use App\Filament\Resources\Clients\Pages\CreateClient;

#[UsesClass(ClientResource::class)]
class ClientResourceTest extends FilamentTestCase
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
    public function it_can_list_clients(): void
    {
        /* Arrange */
        $clients = Client::factory()->createMany(5);

        /* Act */
        $response = Livewire::test(ListClients::class);

        /* Assert */
        $response->assertSuccessful();
        $response->assertCanSeeTableRecords($clients);
    }

    #[Test]
    public function it_can_see_edit_page_of_client(): void
    {
        /* Arrange */
        $client = Client::factory()->create();

        /* Act */
        $response = Livewire::test(EditClient::class, ['record' => $client->getKey()]);

        /* Assert */
        $response->assertSuccessful();
    }

    #[Test]
    public function it_can_create_client(): void
    {
        /* Arrange */
        $user = $this->createUserWithPermission();
        $payload = [
            'name' => 'Test Client',
            'organization_id' => $user->organization->id,
        ];

        /* Act */
        $response = Livewire::test(CreateClient::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $response->assertHasNoFormErrors();
        $response->assertSuccessful();
        $this->assertDatabaseHas('clients', $payload);
    }

    #[Test]
    public function it_cannot_create_client_without_required_fields(): void
    {
        /* Act */
        $response = Livewire::test(CreateClient::class)
            ->fillForm([])
            ->call('create');

        /* Assert */
        $response->assertHasFormErrors(['name', 'organization_id']);
    }

    #[Test]
    public function it_can_edit_client(): void
    {
        /* Arrange */
        $client = Client::factory()->create();
        $payload = [
            'name' => 'Updated Client',
            'organization_id' => $client->organization_id,
        ];

        /* Act */
        $response = Livewire::test(EditClient::class, ['record' => $client->getKey()])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $response->assertHasNoFormErrors();
        $response->assertSuccessful();
        $this->assertDatabaseHas('clients', array_merge($payload, ['id' => $client->id]));
    }

    #[Test]
    public function it_cannot_edit_client_with_invalid_data(): void
    {
        /* Arrange */
        $client = Client::factory()->create();
        $payload = [
            'name' => '',
            'organization_id' => null,
        ];

        /* Act */
        $response = Livewire::test(EditClient::class, ['record' => $client->getKey()])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $response->assertHasFormErrors(['name', 'organization_id']);
    }

    #[Test]
    public function it_can_delete_client(): void
    {
        /* Arrange */
        $client = Client::factory()->create();

        /* Act */
        $response = Livewire::test(EditClient::class, ['record' => $client->getKey()])
            ->callAction('delete');

        /* Assert */
        $response->assertHasNoActionErrors();
        $response->assertSuccessful();
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    #[Test]
    public function it_table_filters_and_sorting(): void
    {
        /* Arrange */
        $user = $this->createUserWithPermission();
        $orgA = $user->organization;
        $orgB = \App\Models\Organization::factory()->create();
        $clientA = Client::factory()->for($orgA)->create(['name' => 'Alpha']);
        $clientB = Client::factory()->for($orgB)->create(['name' => 'Beta']);

        /* Act & Assert */
        $response = Livewire::test(Clients\Pages\ListClients::class)
            ->filterTable('organization', $orgA->id)
            ->assertCanSeeTableRecords([$clientA])
            ->assertCanNotSeeTableRecords([$clientB]);

        // Sorting assertion (no need to override $response)
        Livewire::test(Clients\Pages\ListClients::class)
            ->sortTable('name', 'desc')
            ->assertSuccessful();
    }

    #[Test]
    public function it_bulk_deletes_clients(): void
    {
        /* Arrange */
        $clients = Client::factory(3)->create();
        $ids = $clients->pluck('id')->toArray();

        /* Act */
        $response = Livewire::test(Clients\Pages\ListClients::class)
            ->callAction('delete', $ids);

        /* Assert */
        $response->assertSuccessful();
        foreach ($ids as $id) {
            $this->assertDatabaseMissing('clients', ['id' => $id]);
        }
    }

    #[Test]
    public function it_can_create_client_through_modal(): void
    {
        $user = $this->createUserWithPermission();
        $payload = [
            'name' => 'Modal Client',
            'organization_id' => $user->organization->id,
        ];
        $component = Livewire::test(ListClients::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();
        $component->assertHasNoFormErrors();
        $this->assertDatabaseHas('clients', $payload);
    }

    #[Test]
    public function it_can_edit_client_through_modal(): void
    {
        $client = Client::factory()->create();
        $payload = [
            'name' => 'Edited Modal Client',
            'organization_id' => $client->organization_id,
        ];
        $component = Livewire::test(ListClients::class)
            ->mountAction('edit', ['record' => $client->id])
            ->fillForm($payload)
            ->callMountedAction();
        $component->assertHasNoFormErrors();
        $this->assertDatabaseHas('clients', array_merge($payload, ['id' => $client->id]));
    }

    #[Test]
    public function it_can_delete_client_through_modal(): void
    {
        $client = Client::factory()->create();
        $component = Livewire::test(ListClients::class)
            ->mountAction('delete', ['record' => $client->id])
            ->callMountedAction();
        $component->assertHasNoActionErrors();
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }
}
