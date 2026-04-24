<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = ['task_id', 'user_id', 'action', 'old_value', 'new_value'];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function description(): string
    {
        return match($this->action) {
            'created'          => 'created this task',
            'deleted'          => 'deleted this task',
            'status_changed'   => sprintf(
                'moved from %s to %s',
                \App\Enums\TaskStatus::from($this->old_value['status'])->label(),
                \App\Enums\TaskStatus::from($this->new_value['status'])->label(),
            ),
            'priority_changed' => sprintf(
                'changed priority from %s to %s',
                \App\Enums\TaskPriority::from($this->old_value['priority'])->label(),
                \App\Enums\TaskPriority::from($this->new_value['priority'])->label(),
            ),
            'assigned'         => $this->new_value['assigned_to']
                ? 'assigned the task'
                : 'unassigned the task',
            'title_changed'    => 'updated the title',
            'moved'            => sprintf(
                'moved from %s to %s via board',
                \App\Enums\TaskStatus::from($this->old_value['status'])->label(),
                \App\Enums\TaskStatus::from($this->new_value['status'])->label(),
            ),
            default            => $this->action,
        };
    }
}
