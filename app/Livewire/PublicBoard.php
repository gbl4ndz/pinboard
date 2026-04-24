<?php

namespace App\Livewire;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Live Board')]
class PublicBoard extends Component
{
    public int $projectId = 0;

    public function mount(?string $slug = null): void
    {
        if ($slug) {
            $found = Project::where('slug', $slug)->where('is_public', true)->first();
            $this->projectId = $found?->id ?? 0;
        }

        if ($this->projectId <= 0) {
            $this->projectId = Project::where('is_public', true)->value('id') ?? 0;
        }
    }

    public function selectProject(int $id): void
    {
        $this->projectId = $id;
    }

    // ── Real-time listeners ────────────────────────────────────────────────

    #[On('echo:public-board.{projectId},TaskCreated')]
    public function onTaskCreated(): void {}

    #[On('echo:public-board.{projectId},TaskUpdated')]
    public function onTaskUpdated(): void {}

    #[On('echo:public-board.{projectId},TaskMoved')]
    public function onTaskMoved(): void {}

    #[On('echo:public-board.{projectId},TaskDeleted')]
    public function onTaskDeleted(): void {}

    #[Computed]
    public function project(): ?Project
    {
        return $this->projectId > 0 ? Project::find($this->projectId) : null;
    }

    #[Computed]
    public function publicProjects(): Collection
    {
        return Project::where('is_public', true)->orderBy('name')->get();
    }

    #[Computed]
    public function tasks(): Collection
    {
        if ($this->projectId <= 0) {
            return collect();
        }

        return Task::where('project_id', $this->projectId)
            ->where('is_public', true)
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function columns(): array
    {
        return TaskStatus::ordered();
    }

    #[Computed]
    public function metrics(): array
    {
        $tasks = $this->tasks;

        return array_map(fn(TaskStatus $s) => [
            'status' => $s,
            'count'  => $tasks->filter(fn($t) => $t->status === $s)->count(),
        ], $this->columns);
    }

    public function render()
    {
        return view('livewire.public-board')
            ->layout('layouts.public');
    }
}
