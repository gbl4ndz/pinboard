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

    protected User $manager;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->manager = User::factory()->create();
        $this->manager->assignRole('manager');

        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');
    }

    // ── Index ──────────────────────────────────────────────────────────────

    public function test_manager_can_list_projects(): void
    {
        Project::factory(3)->create(['created_by' => $this->manager->id]);

        $this->actingAs($this->manager)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Projects');
    }

    public function test_staff_can_list_projects(): void
    {
        $this->actingAs($this->staff)
            ->get(route('projects.index'))
            ->assertOk();
    }

    public function test_unauthenticated_user_redirected_from_projects(): void
    {
        $this->get(route('projects.index'))->assertRedirect('/login');
    }

    // ── Create / Store ─────────────────────────────────────────────────────

    public function test_any_user_can_view_project_create_form(): void
    {
        $this->actingAs($this->manager)->get(route('projects.create'))->assertOk();
        $this->actingAs($this->staff)->get(route('projects.create'))->assertOk();
    }

    public function test_manager_can_create_project(): void
    {
        $this->actingAs($this->manager)
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
            'created_by' => $this->manager->id,
        ]);
    }

    public function test_staff_can_create_project(): void
    {
        $this->actingAs($this->staff)
            ->post(route('projects.store'), [
                'name' => 'Staff Project',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'name'       => 'Staff Project',
            'created_by' => $this->staff->id,
        ]);
    }

    public function test_creator_is_auto_added_as_member(): void
    {
        $this->actingAs($this->staff)
            ->post(route('projects.store'), ['name' => 'My Project']);

        $project = \App\Models\Project::where('created_by', $this->staff->id)->first();
        $this->assertTrue($project->hasMember($this->staff));
    }

    public function test_create_project_requires_name(): void
    {
        $this->actingAs($this->manager)
            ->post(route('projects.store'), ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_slug_is_generated_automatically(): void
    {
        $this->actingAs($this->manager)
            ->post(route('projects.store'), ['name' => 'My Great Farm']);

        $this->assertDatabaseHas('projects', ['slug' => 'my-great-farm']);
    }

    public function test_duplicate_slugs_are_made_unique(): void
    {
        Project::factory()->create(['name' => 'Autumn Field', 'slug' => 'autumn-field', 'created_by' => $this->manager->id]);

        $this->actingAs($this->manager)
            ->post(route('projects.store'), ['name' => 'Autumn Field']);

        $this->assertDatabaseHas('projects', ['slug' => 'autumn-field-2']);
    }

    // ── Show ──────────────────────────────────────────────────────────────

    public function test_manager_can_view_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->manager->id]);

        $this->actingAs($this->manager)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee($project->name);
    }

    public function test_staff_can_view_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->manager->id]);
        $project->members()->attach($this->staff->id);

        $this->actingAs($this->staff)
            ->get(route('projects.show', $project))
            ->assertOk();
    }

    public function test_staff_cannot_view_project_they_are_not_a_member_of(): void
    {
        $project = Project::factory()->create(['created_by' => $this->manager->id]);

        $this->actingAs($this->staff)
            ->get(route('projects.show', $project))
            ->assertForbidden();
    }

    // ── Edit / Update ──────────────────────────────────────────────────────

    public function test_manager_can_edit_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->manager->id]);

        $this->actingAs($this->manager)
            ->put(route('projects.update', $project), [
                'name'        => 'Updated Name',
                'description' => 'New description.',
            ])
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Updated Name']);
    }

    public function test_staff_cannot_update_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->manager->id]);

        $this->actingAs($this->staff)
            ->put(route('projects.update', $project), ['name' => 'Hacked'])
            ->assertForbidden();
    }

    // ── Delete ─────────────────────────────────────────────────────────────

    public function test_manager_can_delete_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->manager->id]);

        $this->actingAs($this->manager)
            ->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'));

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_staff_cannot_delete_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->manager->id]);

        $this->actingAs($this->staff)
            ->delete(route('projects.destroy', $project))
            ->assertForbidden();
    }

    // ── Validation ─────────────────────────────────────────────────────────

    public function test_project_name_max_length_is_enforced(): void
    {
        $this->actingAs($this->manager)
            ->post(route('projects.store'), ['name' => str_repeat('a', 256)])
            ->assertSessionHasErrors('name');
    }

    public function test_project_description_can_be_omitted(): void
    {
        $this->actingAs($this->manager)
            ->post(route('projects.store'), ['name' => 'No Desc Farm'])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', ['name' => 'No Desc Farm', 'description' => null]);
    }

    public function test_manager_can_toggle_project_to_public_on_update(): void
    {
        $project = Project::factory()->create([
            'created_by' => $this->manager->id,
            'is_public'  => false,
        ]);

        $this->actingAs($this->manager)
            ->put(route('projects.update', $project), [
                'name'      => $project->name,
                'is_public' => '1',
            ]);

        $this->assertTrue($project->fresh()->is_public);
    }

    public function test_manager_can_toggle_project_to_private_on_update(): void
    {
        $project = Project::factory()->create([
            'created_by' => $this->manager->id,
            'is_public'  => true,
        ]);

        $this->actingAs($this->manager)
            ->put(route('projects.update', $project), [
                'name' => $project->name,
                // is_public omitted → false
            ]);

        $this->assertFalse($project->fresh()->is_public);
    }
}
