<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\TaskBoardService;
use App\Services\TaskOrderingService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CommentsAndActivityTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected User $staff;
    protected Project $project;
    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->manager = User::factory()->create();
        $this->manager->assignRole('manager');

        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');

        $this->project = Project::factory()->create(['created_by' => $this->manager->id]);

        $this->task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
        ]);
    }

    // ── Comments ───────────────────────────────────────────────────────────

    public function test_manager_can_post_comment(): void
    {
        $this->actingAs($this->manager)
            ->post(route('tasks.comments.store', $this->task), ['body' => 'Great progress!'])
            ->assertRedirect(route('tasks.show', $this->task));

        $this->assertDatabaseHas('comments', [
            'task_id' => $this->task->id,
            'user_id' => $this->manager->id,
            'body'    => 'Great progress!',
        ]);
    }

    public function test_staff_can_post_comment(): void
    {
        $this->actingAs($this->staff)
            ->post(route('tasks.comments.store', $this->task), ['body' => 'Working on it'])
            ->assertRedirect();

        $this->assertDatabaseHas('comments', ['body' => 'Working on it', 'user_id' => $this->staff->id]);
    }

    public function test_comment_body_is_required(): void
    {
        $this->actingAs($this->manager)
            ->post(route('tasks.comments.store', $this->task), ['body' => ''])
            ->assertSessionHasErrors('body');
    }

    public function test_comments_shown_on_task_show_page(): void
    {
        Comment::factory()->create([
            'task_id' => $this->task->id,
            'user_id' => $this->manager->id,
            'body'    => 'Visible comment',
        ]);

        $this->actingAs($this->manager)
            ->get(route('tasks.show', $this->task))
            ->assertSee('Visible comment');
    }

    public function test_comments_not_on_public_board(): void
    {
        Comment::factory()->create([
            'task_id' => $this->task->id,
            'user_id' => $this->manager->id,
            'body'    => 'Secret comment',
        ]);

        $this->get(route('public.board'))->assertDontSee('Secret comment');
    }

    // ── Activity logging ───────────────────────────────────────────────────

    public function test_task_creation_logs_created_action(): void
    {
        Event::fake();

        $this->actingAs($this->manager)
            ->post(route('projects.tasks.store', $this->project), [
                'title' => 'New task', 'status' => 'backlog', 'priority' => 'low',
            ]);

        $task = Task::where('title', 'New task')->first();
        $this->assertDatabaseHas('activity_logs', [
            'task_id' => $task->id,
            'action'  => 'created',
        ]);
    }

    public function test_status_change_is_logged(): void
    {
        Event::fake();

        $this->actingAs($this->manager)
            ->put(route('tasks.update', $this->task), [
                'title'    => $this->task->title,
                'status'   => 'done',
                'priority' => $this->task->priority->value,
            ]);

        $this->assertDatabaseHas('activity_logs', [
            'task_id' => $this->task->id,
            'action'  => 'status_changed',
        ]);
    }

    public function test_priority_change_is_logged(): void
    {
        Event::fake();

        $this->task->update(['priority' => 'low']);

        $this->actingAs($this->manager)
            ->put(route('tasks.update', $this->task->fresh()), [
                'title'    => $this->task->title,
                'status'   => $this->task->status->value,
                'priority' => 'high',
            ]);

        $this->assertDatabaseHas('activity_logs', [
            'task_id' => $this->task->id,
            'action'  => 'priority_changed',
        ]);
    }

    public function test_unchanged_fields_are_not_logged(): void
    {
        Event::fake();

        $this->actingAs($this->manager)
            ->put(route('tasks.update', $this->task), [
                'title'    => $this->task->title,
                'status'   => $this->task->status->value,
                'priority' => $this->task->priority->value,
            ]);

        $this->assertDatabaseMissing('activity_logs', [
            'task_id' => $this->task->id,
            'action'  => 'status_changed',
        ]);
    }

    public function test_board_move_logs_moved_action(): void
    {
        Event::fake();

        $service = new TaskBoardService(new TaskOrderingService());
        $service->moveTask($this->task, 'in_progress', [$this->task->id]);

        $this->assertDatabaseHas('activity_logs', [
            'task_id' => $this->task->id,
            'action'  => 'moved',
        ]);
    }

    public function test_board_move_within_same_column_does_not_log(): void
    {
        Event::fake();

        $originalStatus = $this->task->status->value;
        $service        = new TaskBoardService(new TaskOrderingService());
        $service->moveTask($this->task, $originalStatus, [$this->task->id]);

        $this->assertDatabaseMissing('activity_logs', [
            'task_id' => $this->task->id,
            'action'  => 'moved',
        ]);
    }

    public function test_deletion_logs_deleted_action(): void
    {
        Event::fake();

        $this->actingAs($this->manager)
            ->delete(route('tasks.destroy', $this->task));

        $this->assertDatabaseHas('activity_logs', [
            'task_id' => $this->task->id,
            'action'  => 'deleted',
        ]);
    }

    public function test_activity_log_shown_on_task_show_page(): void
    {
        ActivityLog::create([
            'task_id'   => $this->task->id,
            'user_id'   => $this->manager->id,
            'action'    => 'created',
        ]);

        $this->actingAs($this->manager)
            ->get(route('tasks.show', $this->task))
            ->assertSee('created this task');
    }

    public function test_activity_not_shown_on_public_board(): void
    {
        ActivityLog::create([
            'task_id' => $this->task->id,
            'user_id' => $this->manager->id,
            'action'  => 'created',
        ]);

        $this->get(route('public.board'))->assertDontSee('created this task');
    }

    // ── ActivityLogger service ─────────────────────────────────────────────

    public function test_log_changes_records_only_changed_fields(): void
    {
        $logger = new ActivityLogger();

        $this->actingAs($this->manager);

        $before = ['status' => 'backlog', 'priority' => 'medium', 'assigned_to' => null, 'title' => 'Old'];
        $after  = ['status' => 'todo',    'priority' => 'medium', 'assigned_to' => null, 'title' => 'Old'];

        $logger->logChanges($this->task, $before, $after);

        $this->assertDatabaseHas('activity_logs', ['task_id' => $this->task->id, 'action' => 'status_changed']);
        $this->assertDatabaseMissing('activity_logs', ['task_id' => $this->task->id, 'action' => 'priority_changed']);
    }

    public function test_comment_factory_creates_valid_comment(): void
    {
        $comment = Comment::factory()->create([
            'task_id' => $this->task->id,
            'user_id' => $this->manager->id,
        ]);

        $this->assertNotNull($comment->id);
        $this->assertEquals($this->task->id, $comment->task_id);
    }
}
