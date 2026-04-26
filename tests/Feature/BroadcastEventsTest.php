<?php

namespace Tests\Feature;

use App\Events\TaskCreated as TaskCreatedEvent;
use App\Events\TaskDeleted as TaskDeletedEvent;
use App\Events\TaskMoved as TaskMovedEvent;
use App\Events\TaskUpdated as TaskUpdatedEvent;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskBoardService;
use App\Services\TaskOrderingService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BroadcastEventsTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->owner = User::factory()->create();
        $this->owner->assignRole('user');

        $this->project = Project::factory()->create(['created_by' => $this->owner->id]);
    }

    // ── TaskCreated ────────────────────────────────────────────────────────

    public function test_task_created_event_fires_on_store(): void
    {
        Event::fake([TaskCreatedEvent::class]);

        $this->actingAs($this->owner)
            ->post(route('projects.tasks.store', $this->project), [
                'title' => 'New seed', 'status' => 'backlog', 'priority' => 'low',
            ]);

        Event::assertDispatched(TaskCreatedEvent::class);
    }

    public function test_task_created_broadcasts_on_private_channel(): void
    {
        $task  = Task::factory()->create(['project_id' => $this->project->id, 'is_public' => false]);
        $event = new TaskCreatedEvent($task);

        $channels = collect($event->broadcastOn())->map(fn($c) => get_class($c));

        $this->assertContains(PrivateChannel::class, $channels->toArray());
        $this->assertCount(1, $channels); // no public channel when is_public = false
    }

    public function test_task_created_also_broadcasts_on_public_channel_when_public(): void
    {
        $task  = Task::factory()->create(['project_id' => $this->project->id, 'is_public' => true]);
        $event = new TaskCreatedEvent($task);

        $this->assertCount(2, $event->broadcastOn());
    }

    public function test_task_created_event_name_is_clean(): void
    {
        $task  = Task::factory()->create(['project_id' => $this->project->id]);
        $event = new TaskCreatedEvent($task);

        $this->assertEquals('TaskCreated', $event->broadcastAs());
    }

    // ── TaskUpdated ────────────────────────────────────────────────────────

    public function test_task_updated_event_fires_on_update(): void
    {
        Event::fake([TaskUpdatedEvent::class]);
        $task = Task::factory()->create(['project_id' => $this->project->id, 'created_by' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->put(route('tasks.update', $task), [
                'title' => 'Updated', 'status' => 'todo', 'priority' => 'medium',
            ]);

        Event::assertDispatched(TaskUpdatedEvent::class);
    }

    // ── TaskMoved ──────────────────────────────────────────────────────────

    public function test_task_moved_event_fires_on_board_move(): void
    {
        Event::fake([TaskMovedEvent::class]);
        $task = Task::factory()->create(['project_id' => $this->project->id, 'created_by' => $this->owner->id, 'status' => 'backlog']);

        $service = new TaskBoardService(new TaskOrderingService());
        $service->moveTask($task, 'todo', [$task->id]);

        Event::assertDispatched(TaskMovedEvent::class);
    }

    public function test_task_moved_payload_contains_sort_order(): void
    {
        $task  = Task::factory()->create(['project_id' => $this->project->id, 'sort_order' => 200]);
        $event = new TaskMovedEvent($task);

        $this->assertArrayHasKey('sort_order', $event->broadcastWith());
    }

    // ── TaskDeleted ────────────────────────────────────────────────────────

    public function test_task_deleted_event_fires_on_destroy(): void
    {
        Event::fake([TaskDeletedEvent::class]);
        $task = Task::factory()->create(['project_id' => $this->project->id, 'created_by' => $this->owner->id]);

        $this->actingAs($this->owner)->delete(route('tasks.destroy', $task));

        Event::assertDispatched(TaskDeletedEvent::class);
    }

    public function test_task_deleted_does_not_broadcast_on_public_channel_when_private(): void
    {
        $task  = Task::factory()->create(['project_id' => $this->project->id, 'is_public' => false]);
        $event = new TaskDeletedEvent($task->id, $task->project_id, false);

        $this->assertCount(1, $event->broadcastOn());
    }

    // ── Payload safety ─────────────────────────────────────────────────────

    public function test_public_payload_excludes_private_fields(): void
    {
        $task  = Task::factory()->create(['project_id' => $this->project->id]);
        $event = new TaskCreatedEvent($task);

        $payload = $event->broadcastWith();

        $this->assertArrayNotHasKey('created_by', $payload);
        $this->assertArrayNotHasKey('assigned_to', $payload);
        $this->assertArrayNotHasKey('is_public', $payload);
    }

    public function test_public_payload_includes_required_public_fields(): void
    {
        $task  = Task::factory()->create(['project_id' => $this->project->id]);
        $event = new TaskCreatedEvent($task);

        $payload = $event->broadcastWith();

        foreach (['id', 'project_id', 'title', 'status', 'priority', 'updated_at'] as $key) {
            $this->assertArrayHasKey($key, $payload);
        }
    }
}
