<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $taskId,
        public readonly int $projectId,
        public readonly bool $wasPublic,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('board.' . $this->projectId)];

        if ($this->wasPublic) {
            $channels[] = new Channel('public-board.' . $this->projectId);
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        return [
            'id'         => $this->taskId,
            'project_id' => $this->projectId,
        ];
    }

    public function broadcastAs(): string
    {
        return 'TaskDeleted';
    }
}
