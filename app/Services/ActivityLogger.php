<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Task;

class ActivityLogger
{
    public function log(Task $task, string $action, ?array $old = null, ?array $new = null): void
    {
        ActivityLog::create([
            'task_id'   => $task->id,
            'user_id'   => auth()->id(),
            'action'    => $action,
            'old_value' => $old,
            'new_value' => $new,
        ]);
    }

    /**
     * Diff two snapshots and log one entry per changed field.
     *
     * @param array<string,mixed> $before
     * @param array<string,mixed> $after
     */
    public function logChanges(Task $task, array $before, array $after): void
    {
        $actionMap = [
            'status'      => 'status_changed',
            'priority'    => 'priority_changed',
            'assigned_to' => 'assigned',
            'title'       => 'title_changed',
        ];

        foreach ($actionMap as $field => $action) {
            $oldVal = $before[$field] ?? null;
            $newVal = $after[$field]  ?? null;

            // Normalize enum objects to their scalar values for comparison
            if ($oldVal instanceof \BackedEnum) $oldVal = $oldVal->value;
            if ($newVal instanceof \BackedEnum) $newVal = $newVal->value;

            if ($oldVal !== $newVal) {
                $this->log($task, $action, [$field => $oldVal], [$field => $newVal]);
            }
        }
    }
}
