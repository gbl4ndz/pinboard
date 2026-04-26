<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Livewire\BoardPage;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BoardPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $member;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->owner = User::factory()->create();
        $this->owner->assignRole('user');

        $this->member = User::factory()->create();
        $this->member->assignRole('user');

        $this->project = Project::factory()->create(['created_by' => $this->owner->id]);
        $this->project->members()->attach($this->member->id);
    }

    // ── Access ─────────────────────────────────────────────────────────────

    public function test_project_creator_can_access_board(): void
    {
        $this->actingAs($this->owner)
            ->get(route('projects.board', $this->project))
            ->assertOk();
    }

    public function test_project_member_can_access_board(): void
    {
        $this->actingAs($this->member)
            ->get(route('projects.board', $this->project))
            ->assertOk();
    }

    public function test_non_member_cannot_access_board(): void
    {
        $outsider = User::factory()->create()->assignRole('user');

        $this->actingAs($outsider)
            ->get(route('projects.board', $this->project))
            ->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_board(): void
    {
        $this->get(route('projects.board', $this->project))
            ->assertRedirect('/login');
    }

    // ── Rendering ──────────────────────────────────────────────────────────

    public function test_board_renders_all_status_columns(): void
    {
        Livewire::actingAs($this->owner)
            ->test(BoardPage::class, ['project' => $this->project])
            ->assertSee('Backlog')
            ->assertSee('To Do')
            ->assertSee('In Progress')
            ->assertSee('Review')
            ->assertSee('Done');
    }

    public function test_board_shows_tasks_in_correct_column(): void
    {
        Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->owner->id,
            'status'     => TaskStatus::InProgress->value,
            'title'      => 'Growing crop',
        ]);

        Livewire::actingAs($this->owner)
            ->test(BoardPage::class, ['project' => $this->project])
            ->assertSee('Growing crop');
    }

    // ── taskMoved ──────────────────────────────────────────────────────────

    public function test_creator_can_move_any_task(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->owner->id,
            'status'     => TaskStatus::Backlog->value,
            'sort_order' => 100,
        ]);

        Livewire::actingAs($this->owner)
            ->test(BoardPage::class, ['project' => $this->project])
            ->call('taskMoved', $task->id, 'in_progress', [$task->id]);

        $this->assertEquals('in_progress', $task->fresh()->status->value);
    }

    public function test_any_project_member_can_move_any_task(): void
    {
        // Any member can move — not just the creator or assignee
        $task = Task::factory()->create([
            'project_id'  => $this->project->id,
            'created_by'  => $this->owner->id,
            'assigned_to' => null,
            'status'      => TaskStatus::Todo->value,
        ]);

        Livewire::actingAs($this->member)
            ->test(BoardPage::class, ['project' => $this->project])
            ->call('taskMoved', $task->id, 'in_progress', [$task->id]);

        $this->assertEquals('in_progress', $task->fresh()->status->value);
    }

    public function test_task_moved_updates_sort_orders(): void
    {
        $t1 = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->owner->id,
            'status'     => 'todo',
            'sort_order' => 100,
        ]);
        $t2 = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->owner->id,
            'status'     => 'backlog',
            'sort_order' => 100,
        ]);

        Livewire::actingAs($this->owner)
            ->test(BoardPage::class, ['project' => $this->project])
            ->call('taskMoved', $t2->id, 'todo', [$t2->id, $t1->id]);

        $this->assertEquals(100, $t2->fresh()->sort_order);
        $this->assertEquals(200, $t1->fresh()->sort_order);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->owner->id,
            'status'     => 'backlog',
        ]);

        Livewire::actingAs($this->owner)
            ->test(BoardPage::class, ['project' => $this->project])
            ->call('taskMoved', $task->id, 'invalid_status', [$task->id]);

        $this->assertEquals('backlog', $task->fresh()->status->value);
    }
}
