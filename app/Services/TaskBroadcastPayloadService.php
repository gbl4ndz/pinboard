<?php

namespace App\Services;

use App\Models\Task;

class TaskBroadcastPayloadService
{
    /**
     * Safe payload for the public channel — no private metadata.
     */
    public function publicPayload(Task $task): array
    {
        return [
            'id'         => $task->id,
            'project_id' => $task->project_id,
            'title'      => $task->title,
            'status'     => $task->status->value,
            'priority'   => $task->priority->value,
            'due_date'   => $task->due_date?->format('Y-m-d'),
            'updated_at' => $task->updated_at?->toISOString(),
        ];
    }

    /**
     * Richer payload for the private internal channel.
     */
    public function internalPayload(Task $task): array
    {
        return array_merge($this->publicPayload($task), [
            'sort_order'  => $task->sort_order,
            'assigned_to' => $task->assigned_to,
            'created_by'  => $task->created_by,
            'is_public'   => $task->is_public,
        ]);
    }
}
