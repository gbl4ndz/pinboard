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

    protected User $owner;
    protected User $member;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        // $owner created the project and is auto-added as member
        $this->owner = User::factory()->create();
        $this->owner->assignRole('user');

        // $member is a project member (explicitly attached)
        $this->member = User::factory()->create();
        $this->member->assignRole('user');

        $this->project = Project::factory()->create(['created_by' => $this->owner->id]);
        $this->project->members()->attach($this->member->id);
    }

    // ── Create / Store ─────────────────────────────────────────────────────

    public function test_project_creator_can_view_create_form(): void
    {
        $this->actingAs($this->owner)
            ->get(route('projects.tasks.create', $this->project))
            ->assertOk();
    }

    public function test_project_member_can_view_create_form(): void
    {
        $this->actingAs($this->member)
            ->get(route('projects.tasks.create', $this->project))
            ->assertOk();
    }

    public function test_non_member_cannot_view_create_form(): void
    {
        $outsider = User::factory()->create()->assignRole('user');

        $this->actingAs($outsider)
            ->get(route('projects.tasks.create', $this->project))
            ->assertForbidden();
    }

    public function test_project_creator_can_create_task(): void
    {
        $this->actingAs($this->owner)
            ->post(route('projects.tasks.store', $this->project), [
                'title'    => 'Plant the seedlings',
                'status'   => TaskStatus::Todo->value,
                'priority' => TaskPriority::High->value,
            ])
            ->assertRedirect(route('projects.show', $this->project));

        $this->assertDatabaseHas('tasks', [
            'title'      => 'Plant the seedlings',
            'project_id' => $this->project->id,
            'created_by' => $this->owner->id,
            'status'     => 'todo',
        ]);
    }

    public function test_project_member_can_create_task(): void
    {
        $this->actingAs($this->member)
            ->post(route('projects.tasks.store', $this->project), [
                'title'    => 'Water the crops',
                'status'   => TaskStatus::Backlog->value,
                'priority' => TaskPriority::Medium->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'title'      => 'Water the crops',
            'created_by' => $this->member->id,
        ]);
    }

    public function test_non_member_cannot_create_task(): void
    {
        $outsider = User::factory()->create()->assignRole('user');

        $this->actingAs($outsider)
            ->post(route('projects.tasks.store', $this->project), [
                'title'    => 'Sneaky task',
                'status'   => 'todo',
                'priority' => 'low',
            ])
            ->assertForbidden();
    }

    public function test_task_requires_title(): void
    {
        $this->actingAs($this->owner)
            ->post(route('projects.tasks.store', $this->project), [
                'title'    => '',
                'status'   => 'todo',
                'priority' => 'medium',
            ])
            ->assertSessionHasErrors('title');
    }

    public function test_sort_order_is_assigned_automatically(): void
    {
        $this->actingAs($this->owner)->post(route('projects.tasks.store', $this->project), [
            'title' => 'First', 'status' => 'backlog', 'priority' => 'low',
        ]);
        $this->actingAs($this->owner)->post(route('projects.tasks.store', $this->project), [
            'title' => 'Second', 'status' => 'backlog', 'priority' => 'low',
        ]);

        $orders = Task::where('project_id', $this->project->id)->pluck('sort_order')->sort()->values();
        $this->assertEquals([100, 200], $orders->toArray());
    }

    public function test_any_member_can_assign_a_task(): void
    {
        $this->actingAs($this->owner)
            ->post(route('projects.tasks.store', $this->project), [
                'title'       => 'Assigned task',
                'status'      => 'todo',
                'priority'    => 'medium',
                'assigned_to' => $this->member->id,
            ]);

        $this->assertDatabaseHas('tasks', [
            'title'       => 'Assigned task',
            'assigned_to' => $this->member->id,
        ]);
    }

    // ── Show ──────────────────────────────────────────────────────────────

    public function test_project_members_can_view_task(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->owner->id,
        ]);

        $this->actingAs($this->owner)->get(route('tasks.show', $task))->assertOk();
        $this->actingAs($this->member)->get(route('tasks.show', $task))->assertOk();
    }

    public function test_non_member_cannot_view_task(): void
    {
        $outsider = User::factory()->create()->assignRole('user');
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->owner->id,
        ]);

        $this->actingAs($outsider)
            ->get(route('tasks.show', $task))
            ->assertForbidden();
    }

    // ── Update ─────────────────────────────────────────────────────────────

    public function test_task_creator_can_update_their_task(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->member->id,
        ]);

        $this->actingAs($this->member)
            ->put(route('tasks.update', $task), [
                'title'    => 'Updated by creator',
                'status'   => 'in_progress',
                'priority' => 'high',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Updated by creator']);
    }

    public function test_assignee_can_update_task(): void
    {
        $task = Task::factory()->create([
            'project_id'  => $this->project->id,
            'created_by'  => $this->owner->id,
            'assigned_to' => $this->member->id,
        ]);

        $this->actingAs($this->member)
            ->put(route('tasks.update', $task), [
                'title'    => 'Updated by assignee',
                'status'   => 'in_progress',
                'priority' => 'medium',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Updated by assignee']);
    }

    public function test_unrelated_member_cannot_update_task(): void
    {
        $task = Task::factory()->create([
            'project_id'  => $this->project->id,
            'created_by'  => $this->owner->id,
            'assigned_to' => null,
        ]);

        $this->actingAs($this->member)
            ->put(route('tasks.update', $task), [
                'title'    => 'Hacked',
                'status'   => 'done',
                'priority' => 'low',
            ])
            ->assertForbidden();
    }

    // ── Delete ─────────────────────────────────────────────────────────────

    public function test_task_creator_can_delete_their_task(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->owner->id,
        ]);

        $this->actingAs($this->owner)
            ->delete(route('tasks.destroy', $task))
            ->assertRedirect();

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_non_creator_cannot_delete_task(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->owner->id,
        ]);

        $this->actingAs($this->member)
            ->delete(route('tasks.destroy', $task))
            ->assertForbidden();
    }

    // ── Project show lists tasks ───────────────────────────────────────────

    public function test_project_show_displays_tasks(): void
    {
        Task::factory(3)->create([
            'project_id' => $this->project->id,
            'created_by' => $this->owner->id,
        ]);

        $this->actingAs($this->owner)
            ->get(route('projects.show', $this->project))
            ->assertOk()
            ->assertSee('Tasks');
    }
}
