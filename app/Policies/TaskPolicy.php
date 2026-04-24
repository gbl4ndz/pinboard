<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view projects');
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->hasRole('manager')) {
            return true;
        }

        return $task->project->hasMember($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create task');
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->hasPermissionTo('update any task')) {
            return true;
        }

        if ($user->hasPermissionTo('update own task')) {
            return $task->created_by === $user->id;
        }

        return false;
    }

    public function move(User $user, Task $task): bool
    {
        if ($user->hasPermissionTo('move any task')) {
            return true;
        }

        if ($user->hasPermissionTo('move assigned task')) {
            return $task->assigned_to === $user->id;
        }

        return false;
    }

    public function assign(User $user, Task $task): bool
    {
        return $user->hasPermissionTo('assign task');
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->hasPermissionTo('delete task');
    }

    public function comment(User $user, Task $task): bool
    {
        return $user->hasPermissionTo('comment on task');
    }
}
