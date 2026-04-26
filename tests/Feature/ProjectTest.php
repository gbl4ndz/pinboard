<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $other;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->owner = User::factory()->create();
        $this->owner->assignRole('user');

        $this->other = User::factory()->create();
        $this->other->assignRole('user');
    }

    // ── Index ──────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_list_projects(): void
    {
        Project::factory(3)->create(['created_by' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Projects');
    }

    public function test_unauthenticated_user_redirected_from_projects(): void
    {
        $this->get(route('projects.index'))->assertRedirect('/login');
    }

    // ── Create / Store ─────────────────────────────────────────────────────

    public function test_any_user_can_view_project_create_form(): void
    {
        $this->actingAs($this->owner)->get(route('projects.create'))->assertOk();
        $this->actingAs($this->other)->get(route('projects.create'))->assertOk();
    }

    public function test_any_user_can_create_project(): void
    {
        $this->actingAs($this->owner)
            ->post(route('projects.store'), [
                'name'        => 'Spring Harvest',
                'description' => 'Q2 crop planning.',
                'is_public'   => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'name'       => 'Spring Harvest',
            'slug'       => 'spring-harvest',
            'is_public'  => true,
            'created_by' => $this->owner->id,
        ]);
    }

    public function test_creator_is_auto_added_as_member(): void
    {
        $this->actingAs($this->owner)
            ->post(route('projects.store'), ['name' => 'My Project']);

        $project = Project::where('created_by', $this->owner->id)->first();
        $this->assertTrue($project->hasMember($this->owner));
    }

    public function test_create_project_requires_name(): void
    {
        $this->actingAs($this->owner)
            ->post(route('projects.store'), ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_slug_is_generated_automatically(): void
    {
        $this->actingAs($this->owner)
            ->post(route('projects.store'), ['name' => 'My Great Farm']);

        $this->assertDatabaseHas('projects', ['slug' => 'my-great-farm']);
    }

    public function test_duplicate_slugs_are_made_unique(): void
    {
        Project::factory()->create(['name' => 'Autumn Field', 'slug' => 'autumn-field', 'created_by' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->post(route('projects.store'), ['name' => 'Autumn Field']);

        $this->assertDatabaseHas('projects', ['slug' => 'autumn-field-2']);
    }

    // ── Show ──────────────────────────────────────────────────────────────

    public function test_creator_can_view_their_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee($project->name);
    }

    public function test_member_can_view_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->owner->id]);
        $project->members()->attach($this->other->id);

        $this->actingAs($this->other)
            ->get(route('projects.show', $project))
            ->assertOk();
    }

    public function test_non_member_cannot_view_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->owner->id]);

        $this->actingAs($this->other)
            ->get(route('projects.show', $project))
            ->assertForbidden();
    }

    // ── Edit / Update ──────────────────────────────────────────────────────

    public function test_creator_can_edit_their_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->put(route('projects.update', $project), [
                'name'        => 'Updated Name',
                'description' => 'New description.',
            ])
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Updated Name']);
    }

    public function test_non_creator_cannot_update_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->owner->id]);
        $project->members()->attach($this->other->id);

        $this->actingAs($this->other)
            ->put(route('projects.update', $project), ['name' => 'Hacked'])
            ->assertForbidden();
    }

    // ── Delete ─────────────────────────────────────────────────────────────

    public function test_creator_can_delete_their_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'));

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_non_creator_cannot_delete_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->owner->id]);
        $project->members()->attach($this->other->id);

        $this->actingAs($this->other)
            ->delete(route('projects.destroy', $project))
            ->assertForbidden();
    }

    // ── Validation ─────────────────────────────────────────────────────────

    public function test_project_name_max_length_is_enforced(): void
    {
        $this->actingAs($this->owner)
            ->post(route('projects.store'), ['name' => str_repeat('a', 256)])
            ->assertSessionHasErrors('name');
    }

    public function test_project_description_can_be_omitted(): void
    {
        $this->actingAs($this->owner)
            ->post(route('projects.store'), ['name' => 'No Desc Farm'])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', ['name' => 'No Desc Farm', 'description' => null]);
    }

    public function test_creator_can_toggle_project_to_public(): void
    {
        $project = Project::factory()->create([
            'created_by' => $this->owner->id,
            'is_public'  => false,
        ]);

        $this->actingAs($this->owner)
            ->put(route('projects.update', $project), [
                'name'      => $project->name,
                'is_public' => '1',
            ]);

        $this->assertTrue($project->fresh()->is_public);
    }

    public function test_creator_can_toggle_project_to_private(): void
    {
        $project = Project::factory()->create([
            'created_by' => $this->owner->id,
            'is_public'  => true,
        ]);

        $this->actingAs($this->owner)
            ->put(route('projects.update', $project), [
                'name' => $project->name,
                // is_public omitted → false
            ]);

        $this->assertFalse($project->fresh()->is_public);
    }
}
