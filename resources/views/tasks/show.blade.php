<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('projects.show', $project) }}"
                   class="text-stone-400 hover:text-stone-600 text-sm transition shrink-0">
                    ← {{ $project->name }}
                </a>
                <span class="breadcrumb-sep">/</span>
                <h2 class="font-semibold text-xl text-stone-800 leading-tight truncate max-w-md">
                    {{ $task->title }}
                </h2>
            </div>
            @can('update', $task)
                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-md btn-secondary shrink-0">
                    Edit
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="alert-success">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Task detail --}}
            <div class="card p-8">

                {{-- Status + Priority + Public badges --}}
                <div class="flex flex-wrap gap-2 mb-6">
                    <span class="status-badge
                        {{ match($task->status->value) {
                            'done'        => 'status-done',
                            'in_progress' => 'status-in-progress',
                            'review'      => 'status-review',
                            'todo'        => 'status-todo',
                            default       => 'status-backlog',
                        } }}">
                        {{ $task->status->label() }}
                    </span>

                    <span class="priority-pill
                        {{ $task->priority->value === 'high'   ? 'priority-high' :
                           ($task->priority->value === 'medium' ? 'priority-medium' : 'priority-low') }}">
                        {{ $task->priority->label() }} priority
                    </span>

                    @if($task->is_public)
                        <span class="badge badge-green">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                            </svg>
                            Public
                        </span>
                    @endif
                </div>

                {{-- Description --}}
                @if($task->description)
                    <p class="text-stone-600 leading-relaxed whitespace-pre-line">{{ $task->description }}</p>
                @else
                    <p class="text-stone-300 italic text-sm">No description.</p>
                @endif

                {{-- Meta grid --}}
                <dl class="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm border-t border-stone-100 pt-6">
                    <div>
                        <dt class="text-stone-400 text-xs uppercase tracking-wide mb-1">Assignee</dt>
                        <dd class="text-stone-700 font-medium">
                            @if($task->assignee)
                                <div class="flex items-center gap-1.5">
                                    <div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center text-[10px] font-semibold text-green-800">
                                        {{ strtoupper(substr($task->assignee->name, 0, 1)) }}
                                    </div>
                                    {{ $task->assignee->name }}
                                </div>
                            @else
                                <span class="text-stone-400">—</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-stone-400 text-xs uppercase tracking-wide mb-1">Created by</dt>
                        <dd class="text-stone-700 font-medium">{{ $task->creator->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-400 text-xs uppercase tracking-wide mb-1">Due date</dt>
                        <dd class="{{ $task->due_date?->isPast() ? 'text-red-600 font-semibold' : 'text-stone-700 font-medium' }}">
                            {{ $task->due_date ? $task->due_date->format('M j, Y') : '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-stone-400 text-xs uppercase tracking-wide mb-1">Created</dt>
                        <dd class="text-stone-700 font-medium">{{ $task->created_at->format('M j, Y') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Comments ──────────────────────────────────────────────────── --}}
            <div class="card p-8">
                <h3 class="font-semibold text-stone-700 mb-5 flex items-center gap-2">
                    <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    Comments
                    @php $comments = $task->comments()->with('author')->get(); @endphp
                    @if($comments->count())
                        <span class="badge badge-stone">{{ $comments->count() }}</span>
                    @endif
                </h3>

                @if($comments->isEmpty())
                    <p class="text-stone-400 text-sm mb-6">No comments yet. Be the first to add one.</p>
                @else
                    <div class="space-y-5 mb-6">
                        @foreach($comments as $comment)
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-full bg-stone-100 flex items-center justify-center text-xs font-semibold text-stone-600 shrink-0 mt-0.5">
                                    {{ strtoupper(substr($comment->author->name, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-baseline gap-2 mb-1">
                                        <span class="text-sm font-semibold text-stone-800">{{ $comment->author->name }}</span>
                                        <span class="text-xs text-stone-400">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="bg-stone-50 rounded-xl px-4 py-3">
                                        <p class="text-sm text-stone-700 leading-relaxed whitespace-pre-line">{{ $comment->body }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @can('comment', $task)
                    <form method="POST" action="{{ route('tasks.comments.store', $task) }}" class="flex gap-3">
                        @csrf
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-xs font-semibold text-green-800 shrink-0 mt-0.5">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 space-y-2">
                            <textarea name="body" rows="3" required
                                      placeholder="Add a comment…"
                                      class="form-input resize-none @error('body') form-input-error @enderror">{{ old('body') }}</textarea>
                            @error('body') <p class="form-error">{{ $message }}</p> @enderror
                            <button type="submit" class="btn btn-sm btn-primary">Post Comment</button>
                        </div>
                    </form>
                @endcan
            </div>

            {{-- Activity log ───────────────────────────────────────────────── --}}
            @php $logs = $task->activityLogs()->with('actor')->get(); @endphp
            @if($logs->isNotEmpty())
                <div class="card p-8">
                    <h3 class="font-semibold text-stone-700 mb-5 flex items-center gap-2">
                        <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Activity
                    </h3>
                    <ol class="relative border-l border-stone-100 space-y-5 ml-2">
                        @foreach($logs as $log)
                            <li class="pl-5 relative">
                                <span class="absolute -left-[5px] top-1.5 w-2.5 h-2.5 rounded-full bg-stone-200 border-2 border-white ring-1 ring-stone-100"></span>
                                <p class="text-sm text-stone-600 leading-relaxed">
                                    <span class="font-semibold text-stone-800">{{ $log->actor?->name ?? 'System' }}</span>
                                    {{ $log->description() }}
                                </p>
                                <time class="text-xs text-stone-400 mt-0.5 block">{{ $log->created_at->diffForHumans() }}</time>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif

            {{-- Danger zone --}}
            @can('delete', $task)
                <div>
                    <form method="POST" action="{{ route('tasks.destroy', $task) }}"
                          onsubmit="return confirm('Delete this task? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-md btn-danger">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete Task
                        </button>
                    </form>
                </div>
            @endcan

        </div>
    </div>
</x-app-layout>
