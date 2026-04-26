<?php

namespace Tests\Feature;

use App\Livewire\PublicBoard;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicBoardTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->owner = User::factory()->create();
        $this->owner->assignRole('user');
    }

    // ── Accessibility ──────────────────────────────────────────────────────

    public function test_public_board_accessible_without_authentication(): void
    {
        $this->get(route('public.board'))->assertOk();
    }

    public function test_public_board_accessible_with_slug(): void
    {
        $project = Project::factory()->create([
            'is_public'  => true,
            'created_by' => $this->owner->id,
        ]);

        $this->get(route('public.board', ['slug' => $project->slug]))->assertOk();
    }

    // ── Visibility rules ───────────────────────────────────────────────────

    public function test_only_public_tasks_are_shown(): void
    {
        $project = Project::factory()->create(['is_public' => true, 'created_by' => $this->owner->id]);

        $public  = Task::factory()->create([
            'project_id' => $project->id, 'created_by' => $this->owner->id,
            'is_public' => true, 'title' => 'Visible task',
        ]);
        $private = Task::factory()->create([
            'project_id' => $project->id, 'created_by' => $this->owner->id,
            'is_public' => false, 'title' => 'Hidden task',
        ]);

        Livewire::test(PublicBoard::class, ['slug' => $project->slug])
            ->assertSee('Visible task')
            ->assertDontSee('Hidden task');
    }

    public function test_tasks_from_private_projects_not_shown(): void
    {
        $private = Project::factory()->create(['is_public' => false, 'created_by' => $this->owner->id]);
        Task::factory()->create([
            'project_id' => $private->id, 'created_by' => $this->owner->id,
            'is_public' => true, 'title' => 'Should not appear',
        ]);

        // No slug passed — component looks for first public project, finds none
        $component = Livewire::test(PublicBoard::class);

        // The tasks computed should be empty because no public project was selected
        $this->assertEmpty($component->get('tasks') ?? []);
    }

    public function test_private_task_of_public_project_is_hidden(): void
    {
        $project = Project::factory()->create(['is_public' => true, 'created_by' => $this->owner->id]);
        Task::factory()->create([
            'project_id' => $project->id, 'created_by' => $this->owner->id,
            'is_public' => false, 'title' => 'Secret task',
        ]);

        Livewire::test(PublicBoard::class, ['slug' => $project->slug])
            ->assertDontSee('Secret task');
    }

    // ── Farming labels ─────────────────────────────────────────────────────

    public function test_status_labels_are_displayed_on_public_board(): void
    {
        Project::factory()->create(['is_public' => true, 'created_by' => $this->owner->id]);

        Livewire::test(PublicBoard::class)
            ->assertSee('Backlog')
            ->assertSee('To Do')
            ->assertSee('In Progress')
            ->assertSee('Review')
            ->assertSee('Done');
    }

    // ── No edit controls ───────────────────────────────────────────────────

    public function test_no_edit_or_delete_controls_on_public_board(): void
    {
        $project = Project::factory()->create(['is_public' => true, 'created_by' => $this->owner->id]);
        Task::factory()->create([
            'project_id' => $project->id, 'created_by' => $this->owner->id, 'is_public' => true,
        ]);

        Livewire::test(PublicBoard::class, ['slug' => $project->slug])
            ->assertDontSee('Edit')
            ->assertDontSee('Delete');
    }

    // ── Project selector ───────────────────────────────────────────────────

    public function test_project_selector_switches_project(): void
    {
        $p1 = Project::factory()->create(['is_public' => true, 'created_by' => $this->owner->id, 'name' => 'Farm Alpha']);
        $p2 = Project::factory()->create(['is_public' => true, 'created_by' => $this->owner->id, 'name' => 'Farm Beta']);

        Task::factory()->create([
            'project_id' => $p2->id, 'created_by' => $this->owner->id,
            'is_public' => true, 'title' => 'Beta task',
        ]);

        Livewire::test(PublicBoard::class, ['slug' => $p1->slug])
            ->assertDontSee('Beta task')
            ->call('selectProject', $p2->id)
            ->assertSee('Beta task');
    }

    // ── Soft-delete isolation ──────────────────────────────────────────────

    public function test_soft_deleted_task_not_shown_on_public_board(): void
    {
        $project = Project::factory()->create(['is_public' => true, 'created_by' => $this->owner->id]);
        $task    = Task::factory()->create([
            'project_id' => $project->id,
            'created_by' => $this->owner->id,
            'is_public'  => true,
            'title'      => 'Deleted public task',
        ]);

        $task->delete(); // soft delete

        Livewire::test(PublicBoard::class, ['slug' => $project->slug])
            ->assertDontSee('Deleted public task');
    }

    // ── Metrics ────────────────────────────────────────────────────────────

    public function test_metrics_reflect_public_task_counts(): void
    {
        $project = Project::factory()->create(['is_public' => true, 'created_by' => $this->owner->id]);

        Task::factory()->create([
            'project_id' => $project->id, 'created_by' => $this->owner->id,
            'is_public' => true, 'status' => 'done',
        ]);
        Task::factory()->create([
            'project_id' => $project->id, 'created_by' => $this->owner->id,
            'is_public' => false, 'status' => 'done', // private — should not count
        ]);

        $component = Livewire::test(PublicBoard::class, ['slug' => $project->slug]);

        $instance = $component->instance();
        $metrics  = $instance->metrics;
        $done     = collect($metrics)->firstWhere('status', \App\Enums\TaskStatus::Done);
        $this->assertEquals(1, $done['count']);
    }
}
