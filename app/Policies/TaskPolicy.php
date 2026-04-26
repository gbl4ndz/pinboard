<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /** Any authenticated user can reach the tasks index. */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Only project members may view a task. */
    public function view(User $user, Task $task): bool
    {
        return $task->project->hasMember($user);
    }

    /** Any authenticated user may create a task (project membership enforced at the route level). */
    public function create(User $user): bool
    {
        return true;
    }

    /** Task creator or the assigned user may edit a task. */
    public function update(User $user, Task $task): bool
    {
        return $user->id === $task->created_by
            || $user->id === $task->assigned_to;
    }

    /** Any project member may move a task across statuses. */
    public function move(User $user, Task $task): bool
    {
        return $task->project->hasMember($user);
    }

    /** Any project member may assign a task. */
    public function assign(User $user, Task $task): bool
    {
        return $task->project->hasMember($user);
    }

    /** Only the task creator may delete a task. */
    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->created_by;
    }

    /** Any project member may comment on a task. */
    public function comment(User $user, Task $task): bool
    {
        return $task->project->hasMember($user);
    }
}
