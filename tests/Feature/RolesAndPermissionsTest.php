<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        // Re-seed roles/permissions for each test
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->manager = User::factory()->create();
        $this->manager->assignRole('manager');

        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');
    }

    public function test_manager_has_project_permissions(): void
    {
        $this->assertTrue($this->manager->hasPermissionTo('create projects'));
        $this->assertTrue($this->manager->hasPermissionTo('update projects'));
        $this->assertTrue($this->manager->hasPermissionTo('delete projects'));
    }

    public function test_staff_cannot_create_or_delete_projects(): void
    {
        $this->assertFalse($this->staff->hasPermissionTo('create projects'));
        $this->assertFalse($this->staff->hasPermissionTo('delete projects'));
    }

    public function test_staff_can_view_projects(): void
    {
        $this->assertTrue($this->staff->hasPermissionTo('view projects'));
    }

    public function test_manager_can_assign_and_move_any_task(): void
    {
        $this->assertTrue($this->manager->hasPermissionTo('assign task'));
        $this->assertTrue($this->manager->hasPermissionTo('move any task'));
        $this->assertTrue($this->manager->hasPermissionTo('delete task'));
    }

    public function test_staff_can_create_and_update_own_tasks(): void
    {
        $this->assertTrue($this->staff->hasPermissionTo('create task'));
        $this->assertTrue($this->staff->hasPermissionTo('update own task'));
        $this->assertTrue($this->staff->hasPermissionTo('move assigned task'));
    }

    public function test_staff_cannot_delete_or_assign_tasks(): void
    {
        $this->assertFalse($this->staff->hasPermissionTo('delete task'));
        $this->assertFalse($this->staff->hasPermissionTo('assign task'));
    }

    public function test_task_policy_update_allows_manager_on_any_task(): void
    {
        $task = new Task(['created_by' => 9999, 'assigned_to' => 9999]);
        $this->assertTrue($this->manager->can('update', $task));
    }

    public function test_task_policy_update_allows_staff_on_own_task(): void
    {
        $task = new Task(['created_by' => $this->staff->id, 'assigned_to' => 9999]);
        $this->assertTrue($this->staff->can('update', $task));
    }

    public function test_task_policy_update_denies_staff_on_others_task(): void
    {
        $task = new Task(['created_by' => 9999, 'assigned_to' => 9999]);
        $this->assertFalse($this->staff->can('update', $task));
    }

    public function test_task_policy_move_allows_staff_on_assigned_task(): void
    {
        $task = new Task(['created_by' => 9999, 'assigned_to' => $this->staff->id]);
        $this->assertTrue($this->staff->can('move', $task));
    }

    public function test_task_policy_move_denies_staff_on_unassigned_task(): void
    {
        $task = new Task(['created_by' => 9999, 'assigned_to' => 9999]);
        $this->assertFalse($this->staff->can('move', $task));
    }

    public function test_project_policy_view_allows_manager_without_membership(): void
    {
        $project = Project::factory()->create(['created_by' => $this->manager->id]);
        $this->assertTrue($this->manager->can('view', $project));
    }

    public function test_project_policy_view_allows_staff_when_member(): void
    {
        $project = Project::factory()->create(['created_by' => $this->manager->id]);
        $project->members()->attach($this->staff->id);
        $this->assertTrue($this->staff->can('view', $project));
    }

    public function test_project_policy_view_denies_staff_when_not_member(): void
    {
        $project = Project::factory()->create(['created_by' => $this->manager->id]);
        $this->assertFalse($this->staff->can('view', $project));
    }

    public function test_any_authenticated_user_can_create_a_project(): void
    {
        $this->assertTrue($this->staff->can('create', Project::class));
        $this->assertTrue($this->manager->can('create', Project::class));
    }

    public function test_dashboard_redirects_unauthenticated_user(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_accessible_to_authenticated_user(): void
    {
        $this->actingAs($this->manager)->get('/dashboard')->assertOk();
    }

    public function test_public_board_accessible_without_auth(): void
    {
        $this->get('/board')->assertOk();
    }
}
