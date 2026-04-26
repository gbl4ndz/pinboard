<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->owner  = User::factory()->create();
        $this->owner->assignRole('user');

        $this->member = User::factory()->create();
        $this->member->assignRole('user');
    }

    // ── Role setup ─────────────────────────────────────────────────────────

    public function test_single_user_role_exists(): void
    {
        $this->assertTrue(Role::where('name', 'user')->exists());
    }

    public function test_old_manager_role_does_not_exist(): void
    {
        $this->assertFalse(Role::where('name', 'manager')->exists());
    }

    public function test_old_staff_role_does_not_exist(): void
    {
        $this->assertFalse(Role::where('name', 'staff')->exists());
    }

    // ── Project policies ───────────────────────────────────────────────────

    public function test_any_user_can_create_a_project(): void
    {
        $this->assertTrue($this->owner->can('create', Project::class));
        $this->assertTrue($this->member->can('create', Project::class));
    }

    public function test_project_creator_can_view_their_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->owner->id]);
        // Creator is auto-added as member
        $this->assertTrue($this->owner->can('view', $project));
    }

    public function test_project_member_can_view_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->owner->id]);
        $project->members()->attach($this->member->id);

        $this->assertTrue($this->member->can('view', $project));
    }

    public function test_non_member_cannot_view_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->owner->id]);

        $this->assertFalse($this->member->can('view', $project));
    }

    public function test_project_creator_can_update_their_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->owner->id]);

        $this->assertTrue($this->owner->can('update', $project));
    }

    public function test_non_creator_cannot_update_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->owner->id]);
        $project->members()->attach($this->member->id);

        $this->assertFalse($this->member->can('update', $project));
    }

    public function test_project_creator_can_delete_their_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->owner->id]);

        $this->assertTrue($this->owner->can('delete', $project));
    }

    public function test_non_creator_cannot_delete_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->owner->id]);
        $project->members()->attach($this->member->id);

        $this->assertFalse($this->member->can('delete', $project));
    }

    public function test_only_project_creator_can_invite_members(): void
    {
        $project = Project::factory()->create(['created_by' => $this->owner->id]);

        $this->assertTrue($this->owner->can('invite', $project));
        $this->assertFalse($this->member->can('invite', $project));
    }

    // ── Task policies ──────────────────────────────────────────────────────

    public function test_task_creator_can_update_their_task(): void
    {
        $task = new Task(['created_by' => $this->owner->id, 'assigned_to' => null]);

        $this->assertTrue($this->owner->can('update', $task));
    }

    public function test_assignee_can_update_task(): void
    {
        $task = new Task(['created_by' => $this->owner->id, 'assigned_to' => $this->member->id]);

        $this->assertTrue($this->member->can('update', $task));
    }

    public function test_unrelated_user_cannot_update_task(): void
    {
        $other = User::factory()->create()->assignRole('user');
        $task  = new Task(['created_by' => $this->owner->id, 'assigned_to' => $this->member->id]);

        $this->assertFalse($other->can('update', $task));
    }

    public function test_task_creator_can_delete_their_task(): void
    {
        $task = new Task(['created_by' => $this->owner->id, 'assigned_to' => null]);

        $this->assertTrue($this->owner->can('delete', $task));
    }

    public function test_non_creator_cannot_delete_task(): void
    {
        $task = new Task(['created_by' => $this->owner->id, 'assigned_to' => null]);

        $this->assertFalse($this->member->can('delete', $task));
    }

    // ── Dashboard / auth ───────────────────────────────────────────────────

    public function test_dashboard_redirects_unauthenticated_user(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_accessible_to_authenticated_user(): void
    {
        $this->actingAs($this->owner)->get('/dashboard')->assertOk();
    }

    public function test_public_board_accessible_without_auth(): void
    {
        $this->get('/board')->assertOk();
    }
}
