<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
    /** Any project member can invite a new member. */
    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('invite', $project);

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $project->members()->syncWithoutDetaching([$data['user_id']]);

        $user = User::find($data['user_id']);

        return back()->with('success', "{$user->name} added to the project.");
    }

    /** Only the project creator or a manager can remove a member. */
    public function destroy(Project $project, User $user): RedirectResponse
    {
        $this->authorize('update', $project);

        $project->members()->detach($user->id);

        return back()->with('success', "{$user->name} removed from the project.");
    }
}
