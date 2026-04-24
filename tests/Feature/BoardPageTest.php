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

        // Staff must be a project member to access the board
        $this->project->members()->attach($this->staff->id);
    }

    // ── Access ─────────────────────────────────────────────────────────────

    public function test_manager_can_access_board(): void
    {
        $this->actingAs($this->manager)
            ->get(route('projects.board', $this->project))
            ->assertOk();
    }

    public function test_staff_can_access_board(): void
    {
        $this->actingAs($this->staff)
            ->get(route('projects.board', $this->project))
            ->assertOk();
    }

    public function test_unauthenticated_user_cannot_access_board(): void
    {
        $this->get(route('projects.board', $this->project))
            ->assertRedirect('/login');
    }

    // ── Rendering ──────────────────────────────────────────────────────────

    public function test_board_renders_all_status_columns(): void
    {
        Livewire::actingAs($this->manager)
            ->test(BoardPage::class, ['project' => $this->project])
            ->assertSee('Backlog')
            ->assertSee('To Do')
            ->assertSee('In Progress')
            ->assertSee('Review')
            ->assertSee('Done');
    }

    public function test_board_shows_tasks_in_correct_column(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
            'status'     => TaskStatus::InProgress->value,
            'title'      => 'Growing crop',
        ]);

        Livewire::actingAs($this->manager)
            ->test(BoardPage::class, ['project' => $this->project])
            ->assertSee('Growing crop');
    }

    // ── taskMoved ──────────────────────────────────────────────────────────

    public function test_manager_can_move_any_task(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
            'status'     => TaskStatus::Backlog->value,
            'sort_order' => 100,
        ]);

        Livewire::actingAs($this->manager)
            ->test(BoardPage::class, ['project' => $this->project])
            ->call('taskMoved', $task->id, 'in_progress', [$task->id]);

        $this->assertEquals('in_progress', $task->fresh()->status->value);
    }

    public function test_staff_can_move_assigned_task(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
            'assigned_to' => $this->staff->id,
            'status'     => TaskStatus::Todo->value,
        ]);

        Livewire::actingAs($this->staff)
            ->test(BoardPage::class, ['project' => $this->project])
            ->call('taskMoved', $task->id, 'in_progress', [$task->id]);

        $this->assertEquals('in_progress', $task->fresh()->status->value);
    }

    public function test_staff_cannot_move_unassigned_task(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
            'assigned_to' => null,
            'status'     => TaskStatus::Backlog->value,
        ]);

        Livewire::actingAs($this->staff)
            ->test(BoardPage::class, ['project' => $this->project])
            ->call('taskMoved', $task->id, 'in_progress', [$task->id]);

        // Status should remain unchanged
        $this->assertEquals('backlog', $task->fresh()->status->value);
    }

    public function test_task_moved_updates_sort_orders(): void
    {
        $t1 = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
            'status'     => 'todo',
            'sort_order' => 100,
        ]);
        $t2 = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
            'status'     => 'backlog',
            'sort_order' => 100,
        ]);

        // Move t2 into todo, placing it before t1
        Livewire::actingAs($this->manager)
            ->test(BoardPage::class, ['project' => $this->project])
            ->call('taskMoved', $t2->id, 'todo', [$t2->id, $t1->id]);

        $this->assertEquals(100, $t2->fresh()->sort_order);
        $this->assertEquals(200, $t1->fresh()->sort_order);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
            'status'     => 'backlog',
        ]);

        Livewire::actingAs($this->manager)
            ->test(BoardPage::class, ['project' => $this->project])
            ->call('taskMoved', $task->id, 'invalid_status', [$task->id]);

        $this->assertEquals('backlog', $task->fresh()->status->value);
    }
}
