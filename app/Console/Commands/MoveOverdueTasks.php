<?php

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Events\TaskMoved as TaskMovedEvent;
use App\Models\Task;
use App\Services\ActivityLogger;
use Illuminate\Console\Command;

class MoveOverdueTasks extends Command
{
    protected $signature   = 'tasks:move-overdue';
    protected $description = 'Move overdue non-done tasks back to Backlog';

    public function handle(ActivityLogger $logger): int
    {
        $tasks = Task::query()
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->startOfDay())
            ->whereNotIn('status', [TaskStatus::Done->value, TaskStatus::Backlog->value])
            ->get();

        if ($tasks->isEmpty()) {
            $this->info('No overdue tasks to move.');
            return self::SUCCESS;
        }

        foreach ($tasks as $task) {
            $maxOrder = Task::where('project_id', $task->project_id)
                ->where('status', TaskStatus::Backlog->value)
                ->max('sort_order') ?? 0;

            $task->update([
                'status'     => TaskStatus::Backlog->value,
                'sort_order' => $maxOrder + 100,
            ]);

            $logger->log($task->fresh(), 'moved');
            TaskMovedEvent::dispatch($task->fresh());
        }

        $this->info("Moved {$tasks->count()} overdue task(s) to Backlog.");
        return self::SUCCESS;
    }
}
