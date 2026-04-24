<?php

namespace App\Livewire;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskBoardService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Board')]
class BoardPage extends Component
{
    use AuthorizesRequests;

    public Project $project;
    public int $projectId = 0;

    public function mount(Project $project): void
    {
        $this->authorize('view', $project);
        $this->project   = $project;
        $this->projectId = $project->id;
    }

    // ── Real-time listeners ────────────────────────────────────────────────

    #[On('echo-private:board.{projectId},TaskCreated')]
    public function onTaskCreated(): void {}

    #[On('echo-private:board.{projectId},TaskUpdated')]
    public function onTaskUpdated(): void {}

    #[On('echo-private:board.{projectId},TaskMoved')]
    public function onTaskMoved(): void {}

    #[On('echo-private:board.{projectId},TaskDeleted')]
    public function onTaskDeleted(): void {}

    public function taskMoved(int $taskId, string $newStatus, array $orderedIds): void
    {
        $task = Task::findOrFail($taskId);

        if (!auth()->user()->can('move', $task)) {
            return;
        }

        if (!TaskStatus::tryFrom($newStatus)) {
            return;
        }

        app(TaskBoardService::class)->moveTask($task, $newStatus, $orderedIds);
    }

    // ── Computed properties ────────────────────────────────────────────────

    #[Computed]
    public function tasks(): Collection
    {
        return app(TaskBoardService::class)->getBoard($this->project);
    }

    #[Computed]
    public function columns(): array
    {
        return TaskStatus::ordered();
    }

    #[Computed]
    public function stats(): array
    {
        $tasks    = $this->tasks;
        $total    = $tasks->count();
        $done     = $tasks->where('status', TaskStatus::Done)->count();
        $today    = now()->startOfDay();
        $tomorrow = now()->addDay()->startOfDay();

        return [
            'total'     => $total,
            'done'      => $done,
            'percent'   => $total > 0 ? (int) round(($done / $total) * 100) : 0,
            'overdue'   => $tasks->filter(
                fn($t) => $t->due_date
                    && $t->due_date->lt($today)
                    && $t->status !== TaskStatus::Done
            )->count(),
            'today'    => $tasks->filter(
                fn($t) => $t->due_date && $t->due_date->isSameDay($today)
            )->count(),
            'tomorrow' => $tasks->filter(
                fn($t) => $t->due_date && $t->due_date->isSameDay($tomorrow)
            )->count(),
        ];
    }

    #[Computed]
    public function teamMembers(): Collection
    {
        $assignedCounts = $this->tasks
            ->whereNotNull('assigned_to')
            ->groupBy('assigned_to')
            ->map(fn($group) => $group->count());

        return $this->project->members()
            ->orderBy('name')
            ->get()
            ->map(fn($member) => [
                'user'  => $member,
                'count' => $assignedCounts->get($member->id, 0),
            ]);
    }

    public function render()
    {
        return view('livewire.board-page')
            ->layout('layouts.app');
    }
}
