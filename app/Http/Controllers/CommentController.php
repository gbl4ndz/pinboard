<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $this->authorize('comment', $task);

        $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $task->comments()->create([
            'user_id' => $request->user()->id,
            'body'    => $request->body,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Comment added.'], 201);
        }

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Comment added.');
    }
}
