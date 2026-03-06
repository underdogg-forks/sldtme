<?php

namespace Unit\Filament\Resources;

use App\Filament\Resources\Tags\TagResource;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Unit\Filament\FilamentTestCase;
use App\Filament\Resources\Tags\Pages\ListTags;
use App\Filament\Resources\Tags\Pages\EditTag;
use App\Filament\Resources\Tags\Pages\CreateTag;

#[UsesClass(TagResource::class)]
class TagResourceTest extends FilamentTestCase
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
    public function it_can_list_tags(): void
    {
        /* Arrange */
        $tags = Tag::factory()->createMany(5);

        /* Act */
        $response = Livewire::test(ListTags::class);

        /* Assert */
        $response->assertSuccessful();
        $response->assertCanSeeTableRecords($tags);
    }

    #[Test]
    public function it_can_see_edit_page_of_tag(): void
    {
        /* Arrange */
        $tag = Tag::factory()->create();

        /* Act */
        $response = Livewire::test(EditTag::class, ['record' => $tag->getKey()]);

        /* Assert */
        $response->assertSuccessful();
    }

    #[Test]
    public function it_can_create_tag(): void
    {
        /* Arrange */
        $user = $this->createUserWithPermission();
        $payload = [
            'name' => 'Test Tag',
            'organization_id' => $user->organization->id,
        ];

        /* Act */
        $response = Livewire::test(CreateTag::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $response->assertHasNoFormErrors();
        $response->assertSuccessful();
        $this->assertDatabaseHas('tags', $payload);
    }

    #[Test]
    public function it_cannot_create_tag_without_required_fields(): void
    {
        /* Act */
        $response = Livewire::test(CreateTag::class)
            ->fillForm([])
            ->call('create');

        /* Assert */
        $response->assertHasFormErrors(['name', 'organization_id']);
    }

    #[Test]
    public function it_can_edit_tag(): void
    {
        /* Arrange */
        $tag = Tag::factory()->create();
        $payload = [
            'name' => 'Updated Tag',
            'organization_id' => $tag->organization_id,
        ];

        /* Act */
        $response = Livewire::test(EditTag::class, ['record' => $tag->getKey()])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $response->assertHasNoFormErrors();
        $response->assertSuccessful();
        $this->assertDatabaseHas('tags', array_merge($payload, ['id' => $tag->id]));
    }

    #[Test]
    public function it_cannot_edit_tag_with_invalid_data(): void
    {
        /* Arrange */
        $tag = Tag::factory()->create();
        $payload = [
            'name' => '',
            'organization_id' => null,
        ];

        /* Act */
        $response = Livewire::test(EditTag::class, ['record' => $tag->getKey()])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $response->assertHasFormErrors(['name', 'organization_id']);
    }

    #[Test]
    public function it_can_delete_tag(): void
    {
        /* Arrange */
        $tag = Tag::factory()->create();

        /* Act */
        $response = Livewire::test(EditTag::class, ['record' => $tag->getKey()])
            ->callAction('delete');

        /* Assert */
        $response->assertHasNoActionErrors();
        $response->assertSuccessful();
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    #[Test]
    public function it_table_filters_and_sorting(): void
    {
        /* Arrange */
        $user = $this->createUserWithPermission();
        $orgA = $user->organization;
        $orgB = \App\Models\Organization::factory()->create();
        $tagA = Tag::factory()->for($orgA)->create(['name' => 'Alpha']);
        $tagB = Tag::factory()->for($orgB)->create(['name' => 'Beta']);

        /* Act & Assert */
        $response = Livewire::test(ListTags::class)
            ->filterTable('organization', $orgA->id)
            ->assertCanSeeTableRecords([$tagA])
            ->assertCanNotSeeTableRecords([$tagB]);

        Livewire::test(ListTags::class)
            ->sortTable('name', 'desc')
            ->assertSuccessful();
    }

    #[Test]
    public function it_bulk_deletes_tags(): void
    {
        /* Arrange */
        $tags = Tag::factory(3)->create();
        $ids = $tags->pluck('id')->toArray();

        /* Act */
        $response = Livewire::test(ListTags::class)
            ->callAction('delete', $ids);

        /* Assert */
        $response->assertSuccessful();
        foreach ($ids as $id) {
            $this->assertDatabaseMissing('tags', ['id' => $id]);
        }
    }

    #[Test]
    public function it_can_create_tag_through_modal(): void
    {
        $user = $this->createUserWithPermission();
        $payload = [
            'name' => 'Modal Tag',
            'organization_id' => $user->organization->id,
        ];
        $component = Livewire::test(ListTags::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();
        $component->assertHasNoFormErrors();
        $this->assertDatabaseHas('tags', $payload);
    }

    #[Test]
    public function it_can_edit_tag_through_modal(): void
    {
        $tag = Tag::factory()->create();
        $payload = [
            'name' => 'Edited Modal Tag',
            'organization_id' => $tag->organization_id,
        ];
        $component = Livewire::test(ListTags::class)
            ->mountAction('edit', ['record' => $tag->id])
            ->fillForm($payload)
            ->callMountedAction();
        $component->assertHasNoFormErrors();
        $this->assertDatabaseHas('tags', array_merge($payload, ['id' => $tag->id]));
    }

    #[Test]
    public function it_can_delete_tag_through_modal(): void
    {
        $tag = Tag::factory()->create();
        $component = Livewire::test(ListTags::class)
            ->mountAction('delete', ['record' => $tag->id])
            ->callMountedAction();
        $component->assertHasNoActionErrors();
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }
}
