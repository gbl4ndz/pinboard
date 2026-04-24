<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected User $staff;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->manager = User::factory()->create();
        $this->manager->assignRole('manager');

        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');

        $this->project = Project::factory()->create(['created_by' => $this->manager->id]);
    }

    // ── Create / Store ─────────────────────────────────────────────────────

    public function test_manager_can_view_create_form(): void
    {
        $this->actingAs($this->manager)
            ->get(route('projects.tasks.create', $this->project))
            ->assertOk();
    }

    public function test_staff_can_view_create_form(): void
    {
        $this->actingAs($this->staff)
            ->get(route('projects.tasks.create', $this->project))
            ->assertOk();
    }

    public function test_manager_can_create_task(): void
    {
        $this->actingAs($this->manager)
            ->post(route('projects.tasks.store', $this->project), [
                'title'    => 'Plant the seedlings',
                'status'   => TaskStatus::Todo->value,
                'priority' => TaskPriority::High->value,
            ])
            ->assertRedirect(route('projects.show', $this->project));

        $this->assertDatabaseHas('tasks', [
            'title'      => 'Plant the seedlings',
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
            'status'     => 'todo',
        ]);
    }

    public function test_staff_can_create_task(): void
    {
        $this->actingAs($this->staff)
            ->post(route('projects.tasks.store', $this->project), [
                'title'    => 'Water the crops',
                'status'   => TaskStatus::Backlog->value,
                'priority' => TaskPriority::Medium->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'title'      => 'Water the crops',
            'created_by' => $this->staff->id,
        ]);
    }

    public function test_task_requires_title(): void
    {
        $this->actingAs($this->manager)
            ->post(route('projects.tasks.store', $this->project), [
                'title'    => '',
                'status'   => 'todo',
                'priority' => 'medium',
            ])
            ->assertSessionHasErrors('title');
    }

    public function test_sort_order_is_assigned_automatically(): void
    {
        $this->actingAs($this->manager)->post(route('projects.tasks.store', $this->project), [
            'title' => 'First', 'status' => 'backlog', 'priority' => 'low',
        ]);
        $this->actingAs($this->manager)->post(route('projects.tasks.store', $this->project), [
            'title' => 'Second', 'status' => 'backlog', 'priority' => 'low',
        ]);

        $orders = Task::where('project_id', $this->project->id)->pluck('sort_order')->sort()->values();
        $this->assertEquals([100, 200], $orders->toArray());
    }

    public function test_staff_cannot_assign_task_to_another_user(): void
    {
        $other = User::factory()->create();
        $other->assignRole('staff');

        $this->actingAs($this->staff)
            ->post(route('projects.tasks.store', $this->project), [
                'title'       => 'Sneaky assign',
                'status'      => 'todo',
                'priority'    => 'low',
                'assigned_to' => $other->id,
            ]);

        $this->assertDatabaseHas('tasks', ['title' => 'Sneaky assign', 'assigned_to' => null]);
    }

    public function test_manager_can_assign_task(): void
    {
        $this->actingAs($this->manager)
            ->post(route('projects.tasks.store', $this->project), [
                'title'       => 'Assigned task',
                'status'      => 'todo',
                'priority'    => 'medium',
                'assigned_to' => $this->staff->id,
            ]);

        $this->assertDatabaseHas('tasks', [
            'title'       => 'Assigned task',
            'assigned_to' => $this->staff->id,
        ]);
    }

    // ── Show ──────────────────────────────────────────────────────────────

    public function test_both_roles_can_view_task(): void
    {
        $this->project->members()->attach($this->staff->id);
        $task = Task::factory()->create(['project_id' => $this->project->id, 'created_by' => $this->manager->id]);

        $this->actingAs($this->manager)->get(route('tasks.show', $task))->assertOk();
        $this->actingAs($this->staff)->get(route('tasks.show', $task))->assertOk();
    }

    public function test_staff_cannot_view_task_in_unassigned_project(): void
    {
        $task = Task::factory()->create(['project_id' => $this->project->id, 'created_by' => $this->manager->id]);

        $this->actingAs($this->staff)
            ->get(route('tasks.show', $task))
            ->assertForbidden();
    }

    // ── Update ─────────────────────────────────────────────────────────────

    public function test_manager_can_update_any_task(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->staff->id,
        ]);

        $this->actingAs($this->manager)
            ->put(route('tasks.update', $task), [
                'title'    => 'Updated by manager',
                'status'   => 'in_progress',
                'priority' => 'high',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Updated by manager']);
    }

    public function test_staff_can_update_own_task(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->staff->id,
        ]);

        $this->actingAs($this->staff)
            ->put(route('tasks.update', $task), [
                'title'    => 'Staff own edit',
                'status'   => 'todo',
                'priority' => 'low',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Staff own edit']);
    }

    public function test_staff_cannot_update_others_task(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
        ]);

        $this->actingAs($this->staff)
            ->put(route('tasks.update', $task), [
                'title'    => 'Hacked',
                'status'   => 'done',
                'priority' => 'low',
            ])
            ->assertForbidden();
    }

    // ── Delete ─────────────────────────────────────────────────────────────

    public function test_manager_can_delete_task(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
        ]);

        $this->actingAs($this->manager)
            ->delete(route('tasks.destroy', $task))
            ->assertRedirect();

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_staff_cannot_delete_task(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->staff->id,
        ]);

        $this->actingAs($this->staff)
            ->delete(route('tasks.destroy', $task))
            ->assertForbidden();
    }

    // ── Project show lists tasks ───────────────────────────────────────────

    public function test_project_show_displays_tasks(): void
    {
        Task::factory(3)->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
        ]);

        $this->actingAs($this->manager)
            ->get(route('projects.show', $this->project))
            ->assertOk()
            ->assertSee('Tasks');
    }
}
