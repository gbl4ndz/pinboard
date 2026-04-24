<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /** Any authenticated user can see the projects index (filtered in controller). */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Only members may view a project. */
    public function view(User $user, Project $project): bool
    {
        return $project->hasMember($user);
    }

    /** Any authenticated user may create a project. */
    public function create(User $user): bool
    {
        return true;
    }

    /** Only the project creator or a manager may edit. */
    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->created_by
            || $user->hasRole('manager');
    }

    /** Only the project creator or a manager may delete. */
    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->created_by
            || $user->hasRole('manager');
    }

    /** Any project member may invite others. */
    public function invite(User $user, Project $project): bool
    {
        return $project->hasMember($user);
    }
}
