<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Events\TaskCreated as TaskCreatedEvent;
use App\Events\TaskDeleted as TaskDeletedEvent;
use App\Events\TaskUpdated as TaskUpdatedEvent;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function create(Project $project): View
    {
        $this->authorize('create', Task::class);

        $assignableUsers = User::role(['manager', 'staff'])->orderBy('name')->get();

        return view('tasks.create', compact('project', 'assignableUsers'));
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('create', Task::class);

        $data = $request->validate($this->taskRules());

        if (!$request->user()->hasPermissionTo('assign task')) {
            $data['assigned_to'] = null;
        }

        $data['project_id'] = $project->id;
        $data['created_by'] = $request->user()->id;
        $data['is_public']  = $request->boolean('is_public');
        $data['sort_order'] = $this->nextSortOrder($project, $data['status']);

        $task = $project->tasks()->create($data);

        app(ActivityLogger::class)->log($task, 'created');
        TaskCreatedEvent::dispatch($task);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Task created.');
    }

    public function show(Task $task): View
    {
        $this->authorize('view', $task);

        $project = $task->project;

        return view('tasks.show', compact('project', 'task'));
    }

    public function edit(Task $task): View
    {
        $this->authorize('update', $task);

        $project         = $task->project;
        $assignableUsers = User::role(['manager', 'staff'])->orderBy('name')->get();

        return view('tasks.edit', compact('project', 'task', 'assignableUsers'));
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $data = $request->validate($this->taskRules());

        if (!$request->user()->hasPermissionTo('assign task')) {
            unset($data['assigned_to']);
        }

        $data['is_public'] = $request->boolean('is_public');

        $before = $task->only(['status', 'priority', 'assigned_to', 'title']);
        $task->update($data);
        $fresh  = $task->fresh();

        app(ActivityLogger::class)->logChanges($task, $before, $fresh->only(['status', 'priority', 'assigned_to', 'title']));
        TaskUpdatedEvent::dispatch($fresh);

        return redirect()->route('projects.show', $task->project)
            ->with('success', 'Task updated.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $project   = $task->project;
        $taskId    = $task->id;
        $wasPublic = $task->is_public;

        app(ActivityLogger::class)->log($task, 'deleted');
        $task->delete();

        TaskDeletedEvent::dispatch($taskId, $project->id, $wasPublic);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Task deleted.');
    }

    private function taskRules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'status'      => 'required|in:' . implode(',', array_column(TaskStatus::cases(), 'value')),
            'priority'    => 'required|in:' . implode(',', array_column(TaskPriority::cases(), 'value')),
            'assigned_to' => 'nullable|exists:users,id',
            'due_date'    => 'nullable|date',
            'is_public'   => 'boolean',
        ];
    }

    private function nextSortOrder(Project $project, string $status): int
    {
        $max = $project->tasks()
            ->where('status', $status)
            ->max('sort_order') ?? 0;

        return $max + 100;
    }
}
