<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /** Any authenticated user can reach the projects index. */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Only project members may view a project. */
    public function view(User $user, Project $project): bool
    {
        return $project->hasMember($user);
    }

    /** Any authenticated user may create a project. */
    public function create(User $user): bool
    {
        return true;
    }

    /** Only the project creator may edit project settings. */
    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->created_by;
    }

    /** Only the project creator may delete a project. */
    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->created_by;
    }

    /** Only the project creator may add or remove members. */
    public function invite(User $user, Project $project): bool
    {
        return $user->id === $project->created_by;
    }
}
