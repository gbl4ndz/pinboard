<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifies that every route behind auth middleware redirects unauthenticated
 * visitors to the login page rather than returning a 200 or 403.
 */
class GuestAccessTest extends TestCase
{
    use RefreshDatabase;

    protected Project $project;
    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $owner = User::factory()->create();
        $owner->assignRole('user');

        $this->project = Project::factory()->create(['created_by' => $owner->id]);
        $this->task    = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $owner->id,
        ]);
    }

    // ── Dashboard ──────────────────────────────────────────────────────────

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect('/login');
    }

    // ── Projects ───────────────────────────────────────────────────────────

    public function test_guest_cannot_list_projects(): void
    {
        $this->get(route('projects.index'))->assertRedirect('/login');
    }

    public function test_guest_cannot_view_project_create_form(): void
    {
        $this->get(route('projects.create'))->assertRedirect('/login');
    }

    public function test_guest_cannot_create_project(): void
    {
        $this->post(route('projects.store'), ['name' => 'Sneaky'])->assertRedirect('/login');
    }

    public function test_guest_cannot_view_project(): void
    {
        $this->get(route('projects.show', $this->project))->assertRedirect('/login');
    }

    public function test_guest_cannot_view_project_edit_form(): void
    {
        $this->get(route('projects.edit', $this->project))->assertRedirect('/login');
    }

    public function test_guest_cannot_update_project(): void
    {
        $this->put(route('projects.update', $this->project), ['name' => 'Hacked'])->assertRedirect('/login');
    }

    public function test_guest_cannot_delete_project(): void
    {
        $this->delete(route('projects.destroy', $this->project))->assertRedirect('/login');
    }

    public function test_guest_cannot_access_board(): void
    {
        $this->get(route('projects.board', $this->project))->assertRedirect('/login');
    }

    // ── Tasks ──────────────────────────────────────────────────────────────

    public function test_guest_cannot_view_task_create_form(): void
    {
        $this->get(route('projects.tasks.create', $this->project))->assertRedirect('/login');
    }

    public function test_guest_cannot_create_task(): void
    {
        $this->post(route('projects.tasks.store', $this->project), [
            'title' => 'Sneaky task', 'status' => 'backlog', 'priority' => 'low',
        ])->assertRedirect('/login');
    }

    public function test_guest_cannot_view_task(): void
    {
        $this->get(route('tasks.show', $this->task))->assertRedirect('/login');
    }

    public function test_guest_cannot_view_task_edit_form(): void
    {
        $this->get(route('tasks.edit', $this->task))->assertRedirect('/login');
    }

    public function test_guest_cannot_update_task(): void
    {
        $this->put(route('tasks.update', $this->task), [
            'title' => 'Hacked', 'status' => 'done', 'priority' => 'high',
        ])->assertRedirect('/login');
    }

    public function test_guest_cannot_delete_task(): void
    {
        $this->delete(route('tasks.destroy', $this->task))->assertRedirect('/login');
    }

    // ── Comments ───────────────────────────────────────────────────────────

    public function test_guest_cannot_post_comment(): void
    {
        $this->post(route('tasks.comments.store', $this->task), ['body' => 'Lurker comment'])
             ->assertRedirect('/login');
    }

    // ── Profile ────────────────────────────────────────────────────────────

    public function test_guest_cannot_view_profile(): void
    {
        $this->get(route('profile.edit'))->assertRedirect('/login');
    }

    public function test_guest_cannot_update_profile(): void
    {
        $this->patch(route('profile.update'), ['name' => 'Ghost'])->assertRedirect('/login');
    }

    public function test_guest_cannot_delete_account(): void
    {
        $this->delete(route('profile.destroy'), ['password' => 'password'])->assertRedirect('/login');
    }
}
