<?php

namespace App\Services;

use App\Events\TaskMoved as TaskMovedEvent;
use App\Models\Project;
use App\Models\Task;
use App\Services\ActivityLogger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TaskBoardService
{
    public function __construct(private readonly TaskOrderingService $ordering) {}

    /**
     * Return all non-deleted tasks for the board, ordered by sort_order.
     */
    public function getBoard(Project $project): Collection
    {
        return $project->tasks()
            ->with(['assignee'])
            ->withCount('comments')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Move a task to a new status column and re-apply the order of that column.
     *
     * @param int[] $orderedIds  Task IDs in their new visual order for the destination column.
     */
    public function moveTask(Task $task, string $newStatus, array $orderedIds): void
    {
        $oldStatus = $task->status->value;

        DB::transaction(function () use ($task, $newStatus, $orderedIds) {
            $task->update(['status' => $newStatus]);
            $this->ordering->applyOrder($orderedIds);
        });

        $fresh = $task->fresh();

        if ($oldStatus !== $newStatus) {
            app(ActivityLogger::class)->log(
                $fresh, 'moved',
                ['status' => $oldStatus],
                ['status' => $newStatus],
            );
        }

        TaskMovedEvent::dispatch($fresh);
    }
}
