<?php

namespace Tests\Feature;

use App\Livewire\BoardPage;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Edge-case tests for task validation, soft-delete isolation, and
 * permission enforcement not covered in the main TaskTest.
 */
class TaskHardeningTest extends TestCase
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

    // ── Validation ─────────────────────────────────────────────────────────

    public function test_task_title_max_length_is_enforced(): void
    {
        $this->actingAs($this->owner)
            ->post(route('projects.tasks.store', $this->project), [
                'title'    => str_repeat('a', 256),
                'status'   => 'backlog',
                'priority' => 'low',
            ])
            ->assertSessionHasErrors('title');
    }

    public function test_invalid_task_status_is_rejected_on_create(): void
    {
        $this->actingAs($this->owner)
            ->post(route('projects.tasks.store', $this->project), [
                'title'    => 'Bad status task',
                'status'   => 'flying',
                'priority' => 'low',
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_invalid_task_priority_is_rejected_on_create(): void
    {
        $this->actingAs($this->owner)
            ->post(route('projects.tasks.store', $this->project), [
                'title'    => 'Bad priority task',
                'status'   => 'backlog',
                'priority' => 'critical',
            ])
            ->assertSessionHasErrors('priority');
    }

    public function test_invalid_task_status_is_rejected_on_update(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->owner->id,
        ]);

        $this->actingAs($this->owner)
            ->put(route('tasks.update', $task), [
                'title'    => $task->title,
                'status'   => 'not_a_status',
                'priority' => 'low',
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_invalid_task_priority_is_rejected_on_update(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->owner->id,
        ]);

        $this->actingAs($this->owner)
            ->put(route('tasks.update', $task), [
                'title'    => $task->title,
                'status'   => 'backlog',
                'priority' => 'extreme',
            ])
            ->assertSessionHasErrors('priority');
    }

    public function test_due_date_must_be_a_valid_date(): void
    {
        $this->actingAs($this->owner)
            ->post(route('projects.tasks.store', $this->project), [
                'title'    => 'Date test',
                'status'   => 'backlog',
                'priority' => 'low',
                'due_date' => 'not-a-date',
            ])
            ->assertSessionHasErrors('due_date');
    }

    public function test_is_public_defaults_to_false_when_not_submitted(): void
    {
        Event::fake();

        $this->actingAs($this->owner)
            ->post(route('projects.tasks.store', $this->project), [
                'title'    => 'No public flag',
                'status'   => 'backlog',
                'priority' => 'low',
            ]);

        $this->assertDatabaseHas('tasks', [
            'title'     => 'No public flag',
            'is_public' => false,
        ]);
    }

    public function test_task_description_can_be_empty(): void
    {
        Event::fake();

        $this->actingAs($this->owner)
            ->post(route('projects.tasks.store', $this->project), [
                'title'       => 'No description',
                'status'      => 'backlog',
                'priority'    => 'low',
                'description' => '',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', ['title' => 'No description', 'description' => null]);
    }

    // ── Soft-delete isolation ──────────────────────────────────────────────

    public function test_soft_deleted_task_not_shown_on_project_show(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->owner->id,
            'title'      => 'Ghost task',
        ]);

        Event::fake();
        $task->delete();

        $this->actingAs($this->owner)
            ->get(route('projects.show', $this->project))
            ->assertDontSee('Ghost task');
    }

    public function test_soft_deleted_task_not_shown_on_board(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->owner->id,
            'title'      => 'Deleted card',
        ]);

        Event::fake();
        $task->delete();

        Livewire::actingAs($this->owner)
            ->test(BoardPage::class, ['project' => $this->project])
            ->assertDontSee('Deleted card');
    }

    public function test_soft_deleted_task_returns_404_on_show(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->owner->id,
        ]);

        Event::fake();
        $task->delete();

        $this->actingAs($this->owner)
            ->get(route('tasks.show', $task))
            ->assertNotFound();
    }
}
