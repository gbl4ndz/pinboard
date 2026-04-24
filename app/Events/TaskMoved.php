<?php

namespace App\Events;

use App\Models\Task;
use App\Services\TaskBroadcastPayloadService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskMoved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Task $task) {}

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('board.' . $this->task->project_id)];

        if ($this->task->is_public) {
            $channels[] = new Channel('public-board.' . $this->task->project_id);
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        return app(TaskBroadcastPayloadService::class)->internalPayload($this->task);
    }

    public function broadcastAs(): string
    {
        return 'TaskMoved';
    }
}
