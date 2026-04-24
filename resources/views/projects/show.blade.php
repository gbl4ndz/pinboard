<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('projects.index') }}"
                   class="text-stone-400 hover:text-stone-600 text-sm transition shrink-0">← Projects</a>
                <span class="breadcrumb-sep">/</span>
                <h2 class="font-semibold text-xl text-stone-800 leading-tight truncate">{{ $project->name }}</h2>
                @if($project->is_public)
                    <span class="badge badge-green shrink-0">Public</span>
                @else
                    <span class="badge badge-stone shrink-0">Internal</span>
                @endif
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('projects.board', $project) }}" class="btn btn-md btn-primary">
                    Open Board
                </a>
                @can('update', $project)
                    <a href="{{ route('projects.edit', $project) }}" class="btn btn-md btn-secondary">
                        Edit
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="alert-success">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Project meta --}}
            <div class="card p-8">
                @if($project->description)
                    <p class="text-stone-600 leading-relaxed">{{ $project->description }}</p>
                @else
                    <p class="text-stone-300 italic text-sm">No description.</p>
                @endif

                <dl class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm border-t border-stone-100 pt-6">
                    <div>
                        <dt class="text-stone-400 text-xs uppercase tracking-wide mb-1">Created by</dt>
                        <dd class="text-stone-700 font-medium">{{ $project->creator->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-400 text-xs uppercase tracking-wide mb-1">Created</dt>
                        <dd class="text-stone-700 font-medium">{{ $project->created_at->format('M j, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-400 text-xs uppercase tracking-wide mb-1">Visibility</dt>
                        <dd class="font-medium {{ $project->is_public ? 'text-green-700' : 'text-stone-500' }}">
                            {{ $project->is_public ? 'Public' : 'Internal' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-stone-400 text-xs uppercase tracking-wide mb-1">Slug</dt>
                        <dd class="text-stone-400 font-mono text-xs">{{ $project->slug }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Members panel --}}
            <div class="card p-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="font-semibold text-stone-800">Members</h3>
                        <p class="text-xs text-stone-400 mt-0.5">
                            Members can access this project and all its tasks.
                            @can('update', $project)
                                Only you can remove members or edit this project.
                            @endcan
                        </p>
                    </div>
                    <span class="badge badge-stone">
                        {{ $project->members->count() }} {{ \Illuminate\Support\Str::plural('member', $project->members->count()) }}
                    </span>
                </div>

                {{-- Current members list --}}
                <div class="space-y-2 {{ $nonMembers->isNotEmpty() && auth()->user()->can('invite', $project) ? 'mb-6' : '' }}">
                    @foreach($project->members as $member)
                        @php
                            $colors = ['bg-violet-600','bg-blue-600','bg-emerald-600','bg-orange-500','bg-rose-500'];
                            $color  = $colors[$member->id % count($colors)];
                            $isCreator = $member->id === $project->created_by;
                        @endphp
                        <div class="flex items-center justify-between gap-4 px-4 py-3 rounded-xl border border-stone-100 hover:border-stone-200 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-full {{ $color }} flex items-center justify-center text-xs font-bold text-white shrink-0">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-stone-800 truncate flex items-center gap-2">
                                        {{ $member->name }}
                                        @if($isCreator)
                                            <span class="badge badge-green text-[10px]">Owner</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-stone-400 truncate">{{ $member->email }}</div>
                                </div>
                            </div>
                            {{-- Remove: only creator/manager can remove; can't remove yourself if you're the creator --}}
                            @can('update', $project)
                                @if(!$isCreator)
                                    <form method="POST"
                                          action="{{ route('projects.members.destroy', [$project, $member]) }}"
                                          onsubmit="return confirm('Remove {{ addslashes($member->name) }} from this project?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="text-xs text-stone-400 hover:text-red-600 transition">
                                            Remove
                                        </button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    @endforeach
                </div>

                {{-- Add member form — any member can invite --}}
                @can('invite', $project)
                    @if($nonMembers->isNotEmpty())
                        <form method="POST" action="{{ route('projects.members.store', $project) }}"
                              class="flex items-center gap-3 pt-4 border-t border-stone-100">
                            @csrf
                            <select name="user_id" class="form-input flex-1 py-2 text-sm">
                                <option value="">— Select a person to invite —</option>
                                @foreach($nonMembers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} &lt;{{ $u->email }}&gt;</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                Invite
                            </button>
                        </form>
                    @else
                        <p class="text-xs text-stone-400 pt-4 border-t border-stone-100">Everyone is already a member.</p>
                    @endif
                @endcan
            </div>

            {{-- Tasks panel --}}
            <div class="card p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-semibold text-stone-700">Tasks</h3>
                    @can('create', App\Models\Task::class)
                        <a href="{{ route('projects.tasks.create', $project) }}" class="btn btn-sm btn-primary">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            New Task
                        </a>
                    @endcan
                </div>

                @php
                    $tasks   = $project->tasks()->with(['assignee', 'creator'])->orderBy('sort_order')->get();
                    $grouped = $tasks->groupBy(fn($t) => $t->status->value);
                @endphp

                @if($tasks->isEmpty())
                    <div class="text-center py-12">
                        <div class="text-4xl mb-3 select-none">📋</div>
                        <p class="text-stone-400 text-sm">No tasks yet.</p>
                        @can('create', App\Models\Task::class)
                            <a href="{{ route('projects.tasks.create', $project) }}"
                               class="btn btn-sm btn-primary mt-4 mx-auto">Add first task</a>
                        @endcan
                    </div>
                @else
                    <div class="space-y-5">
                        @foreach(\App\Enums\TaskStatus::ordered() as $status)
                            @if(isset($grouped[$status->value]))
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="w-2 h-2 rounded-full
                                            {{ $status->value === 'done'        ? 'bg-green-500' :
                                               ($status->value === 'in_progress'? 'bg-yellow-400' :
                                               ($status->value === 'review'     ? 'bg-orange-400' :
                                               ($status->value === 'todo'       ? 'bg-blue-400' : 'bg-stone-300'))) }}">
                                        </span>
                                        <span class="text-xs font-semibold text-stone-400 uppercase tracking-wide">
                                            {{ $status->label() }}
                                        </span>
                                        <span class="text-xs text-stone-300">
                                            {{ count($grouped[$status->value]) }}
                                        </span>
                                    </div>
                                    <div class="space-y-1.5">
                                        @foreach($grouped[$status->value] as $task)
                                            <div class="flex items-center justify-between gap-4 border border-stone-100 rounded-xl px-4 py-3 hover:border-stone-200 hover:bg-stone-50 transition">
                                                <div class="flex-1 min-w-0">
                                                    <a href="{{ route('tasks.show', $task) }}"
                                                       class="font-medium text-stone-800 hover:text-green-700 transition text-sm truncate block">
                                                        {{ $task->title }}
                                                    </a>
                                                    <div class="flex items-center gap-3 mt-1 text-xs text-stone-400">
                                                        @if($task->assignee)
                                                            <span class="flex items-center gap-1">
                                                                <span class="w-3.5 h-3.5 rounded-full bg-green-100 inline-flex items-center justify-center text-[9px] font-bold text-green-800">
                                                                    {{ strtoupper(substr($task->assignee->name, 0, 1)) }}
                                                                </span>
                                                                {{ $task->assignee->name }}
                                                            </span>
                                                        @endif
                                                        @if($task->due_date)
                                                            <span class="{{ $task->due_date->isPast() ? 'text-red-400 font-medium' : '' }}">
                                                                Due {{ $task->due_date->format('M j') }}
                                                            </span>
                                                        @endif
                                                        <span class="priority-pill text-[10px]
                                                            {{ $task->priority->value === 'high'   ? 'priority-high' :
                                                               ($task->priority->value === 'medium' ? 'priority-medium' : 'priority-low') }}">
                                                            {{ $task->priority->label() }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 shrink-0">
                                                    @can('update', $task)
                                                        <a href="{{ route('tasks.edit', $task) }}"
                                                           class="btn btn-sm btn-secondary py-1 px-2.5 text-xs">Edit</a>
                                                    @endcan
                                                    @can('delete', $task)
                                                        <form method="POST" action="{{ route('tasks.destroy', $task) }}"
                                                              onsubmit="return confirm('Delete this task?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit"
                                                                    class="btn btn-sm btn-danger py-1 px-2.5 text-xs">Delete</button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
