<div class="max-w-full px-6 py-6">

    {{-- Livewire loading bar --}}
    <div wire:loading.delay wire:target="taskMoved" class="loading-bar"></div>

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-5 px-1">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('projects.show', $this->project) }}"
               class="text-stone-400 hover:text-stone-600 text-sm transition shrink-0 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                {{ $this->project->name }}
            </a>
            <span class="breadcrumb-sep">/</span>
            <h2 class="font-bold text-xl text-stone-900 truncate">Board</h2>
            @if($this->project->is_public)
                <a href="{{ route('public.board', ['slug' => $this->project->slug]) }}"
                   target="_blank"
                   class="btn btn-sm btn-secondary shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Public view
                </a>
            @endif
        </div>

        <div class="flex items-center gap-4 shrink-0">
            {{-- Team avatars --}}
            @if($this->teamMembers->isNotEmpty())
                <div class="flex items-center gap-2">
                    <div class="flex -space-x-2">
                        @foreach($this->teamMembers->take(5) as $member)
                            @php
                                $colors = ['bg-violet-600','bg-blue-600','bg-emerald-600','bg-orange-500','bg-rose-500'];
                                $color  = $colors[$loop->index % count($colors)];
                            @endphp
                            <div class="w-7 h-7 rounded-full {{ $color }} ring-2 ring-white
                                        flex items-center justify-center text-[10px] font-bold text-white
                                        relative group cursor-default"
                                 title="{{ $member['user']->name }} — {{ $member['count'] }} {{ Str::plural('task', $member['count']) }}">
                                {{ strtoupper(substr($member['user']->name, 0, 1)) }}
                                {{-- Tooltip --}}
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block
                                            bg-stone-900 text-white text-xs rounded-lg px-2.5 py-1.5 whitespace-nowrap z-10
                                            pointer-events-none shadow-lg">
                                    {{ $member['user']->name }}
                                    <span class="text-stone-400 ml-1">{{ $member['count'] }} {{ Str::plural('task', $member['count']) }}</span>
                                </div>
                            </div>
                        @endforeach
                        @if($this->teamMembers->count() > 5)
                            <div class="w-7 h-7 rounded-full bg-stone-200 ring-2 ring-white
                                        flex items-center justify-center text-[10px] font-bold text-stone-600">
                                +{{ $this->teamMembers->count() - 5 }}
                            </div>
                        @endif
                    </div>
                    <span class="text-xs text-stone-400">{{ $this->teamMembers->count() }} {{ Str::plural('member', $this->teamMembers->count()) }}</span>
                </div>
            @endif

            @can('create', App\Models\Task::class)
                <a href="{{ route('projects.tasks.create', $this->project) }}"
                   class="btn btn-sm btn-primary">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Task
                </a>
            @endcan
        </div>
    </div>

    {{-- ── Stats strip ─────────────────────────────────────────────────────── --}}
    @if($this->stats['total'] > 0)
        <div class="bg-white rounded-2xl border border-stone-200 px-5 py-4 mb-6 flex items-center gap-6 flex-wrap">

            {{-- Progress bar --}}
            <div class="flex-1 min-w-[200px]">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-semibold text-stone-600">
                        Progress
                    </span>
                    <span class="text-xs font-bold tabular-nums
                        {{ $this->stats['percent'] === 100 ? 'text-green-600' : 'text-stone-700' }}">
                        {{ $this->stats['done'] }}/{{ $this->stats['total'] }} done
                        &middot; {{ $this->stats['percent'] }}%
                    </span>
                </div>
                <div class="h-2 bg-stone-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500
                        {{ $this->stats['percent'] === 100 ? 'bg-green-500' : 'bg-green-600' }}"
                         style="width: {{ $this->stats['percent'] }}%"></div>
                </div>
            </div>

            {{-- Divider --}}
            <div class="h-8 w-px bg-stone-100 hidden sm:block"></div>

            {{-- Alert pills --}}
            <div class="flex items-center gap-2 flex-wrap shrink-0">
                @if($this->stats['overdue'] > 0)
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold
                                 bg-red-50 text-red-600 border border-red-200 rounded-lg px-3 py-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $this->stats['overdue'] }} overdue
                    </span>
                @endif
                @if($this->stats['today'] > 0)
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold
                                 bg-orange-50 text-orange-600 border border-orange-200 rounded-lg px-3 py-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $this->stats['today'] }} due today
                    </span>
                @endif
                @if($this->stats['tomorrow'] > 0)
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold
                                 bg-amber-50 text-amber-600 border border-amber-200 rounded-lg px-3 py-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ $this->stats['tomorrow'] }} due tomorrow
                    </span>
                @endif
                @if($this->stats['overdue'] === 0 && $this->stats['today'] === 0 && $this->stats['tomorrow'] === 0)
                    <span class="text-xs text-stone-400">No upcoming deadlines</span>
                @endif
            </div>

        </div>
    @endif

    {{-- ── Kanban columns ───────────────────────────────────────────────────── --}}
    <div class="flex gap-6 overflow-x-auto pb-6 items-start" wire:ignore.self>

        @foreach($this->columns as $status)
            @php
                $columnTasks = $this->tasks
                    ->where('status', $status)
                    ->sortBy('sort_order')
                    ->values();

                $dotColor = match($status->value) {
                    'done'        => 'bg-green-500',
                    'in_progress' => 'bg-yellow-400',
                    'review'      => 'bg-orange-400',
                    'todo'        => 'bg-blue-400',
                    default       => 'bg-stone-300',
                };

                $today    = now()->startOfDay();
                $tomorrow = now()->addDay()->startOfDay();
            @endphp

            <div class="flex-shrink-0 w-72 flex flex-col" wire:key="col-{{ $status->value }}">

                {{-- Column header --}}
                <div class="flex items-center justify-between mb-3 px-0.5">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $dotColor }}"></span>
                        <h3 class="text-sm font-semibold text-stone-800">{{ $status->label() }}</h3>
                        <span class="text-xs text-stone-400 tabular-nums">({{ $columnTasks->count() }})</span>
                    </div>
                    @can('create', App\Models\Task::class)
                        <a href="{{ route('projects.tasks.create', $this->project) }}?status={{ $status->value }}"
                           class="flex items-center gap-1 text-xs text-stone-400 hover:text-stone-700 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add
                        </a>
                    @endcan
                </div>

                {{-- Task list --}}
                <div class="task-list space-y-2.5 min-h-24 flex-1"
                     data-status="{{ $status->value }}">

                    @foreach($columnTasks as $task)
                        @php
                            $isOverdue   = $task->due_date && $task->due_date->lt($today) && $task->status !== \App\Enums\TaskStatus::Done;
                            $isDueToday  = $task->due_date && $task->due_date->isSameDay($today);
                            $isDueTomorrow = $task->due_date && $task->due_date->isSameDay($tomorrow);
                        @endphp

                        <div class="saas-task-card animate-slide-up
                                    {{ $isOverdue ? 'border-red-200 bg-red-50/30' : '' }}"
                             data-task-id="{{ $task->id }}"
                             wire:key="task-{{ $task->id }}">

                            {{-- Top row: priority + due badge --}}
                            <div class="flex items-center justify-between mb-2.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full
                                        {{ $task->priority->value === 'high'   ? 'bg-red-400' :
                                           ($task->priority->value === 'medium' ? 'bg-amber-400' : 'bg-stone-300') }}">
                                    </span>
                                    <span class="text-[11px] font-semibold uppercase tracking-wide
                                        {{ $task->priority->value === 'high'   ? 'text-red-500' :
                                           ($task->priority->value === 'medium' ? 'text-amber-600' : 'text-stone-400') }}">
                                        {{ $task->priority->label() }}
                                    </span>
                                </div>

                                {{-- Due badge --}}
                                @if($isOverdue)
                                    <span class="text-[10px] font-bold uppercase tracking-wide bg-red-100 text-red-600 rounded-md px-1.5 py-0.5">
                                        Overdue
                                    </span>
                                @elseif($isDueToday)
                                    <span class="text-[10px] font-bold uppercase tracking-wide bg-orange-100 text-orange-600 rounded-md px-1.5 py-0.5">
                                        Today
                                    </span>
                                @elseif($isDueTomorrow)
                                    <span class="text-[10px] font-bold uppercase tracking-wide bg-amber-100 text-amber-600 rounded-md px-1.5 py-0.5">
                                        Tomorrow
                                    </span>
                                @endif
                            </div>

                            {{-- Title --}}
                            <a href="{{ route('tasks.show', $task) }}"
                               class="block font-semibold text-stone-900 hover:text-green-700 text-sm leading-snug mb-1.5 transition"
                               onclick="event.stopPropagation()">
                                {{ $task->title }}
                            </a>

                            {{-- Description excerpt --}}
                            @if($task->description)
                                <p class="text-xs text-stone-400 leading-relaxed mb-3 line-clamp-2">
                                    {{ $task->description }}
                                </p>
                            @endif

                            {{-- Bottom row --}}
                            <div class="flex items-center justify-between gap-2 pt-2.5 border-t border-stone-100 mt-2">

                                {{-- Assignee --}}
                                @if($task->assignee)
                                    @php
                                        $colors = ['bg-violet-600','bg-blue-600','bg-emerald-600','bg-orange-500','bg-rose-500'];
                                        $avatarColor = $colors[$task->assignee->id % count($colors)];
                                    @endphp
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <div class="w-5 h-5 rounded-full {{ $avatarColor }} flex items-center justify-center
                                                    text-[9px] font-bold text-white shrink-0"
                                             title="{{ $task->assignee->name }}">
                                            {{ strtoupper(substr($task->assignee->name, 0, 1)) }}
                                        </div>
                                        <span class="text-xs text-stone-500 truncate">{{ $task->assignee->name }}</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-5 h-5 rounded-full border-2 border-dashed border-stone-200
                                                    flex items-center justify-center">
                                            <svg class="w-2.5 h-2.5 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                        <span class="text-xs text-stone-300">Unassigned</span>
                                    </div>
                                @endif

                                {{-- Comments + date --}}
                                <div class="flex items-center gap-2.5 text-stone-400 shrink-0">
                                    @if($task->comments_count > 0)
                                        <span class="flex items-center gap-1 text-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                            </svg>
                                            {{ $task->comments_count }}
                                        </span>
                                    @endif
                                    @if($task->due_date && !$isOverdue && !$isDueToday && !$isDueTomorrow)
                                        <span class="flex items-center gap-1 text-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $task->due_date->format('M j') }}
                                        </span>
                                    @endif
                                </div>

                            </div>

                        </div>
                    @endforeach

                    {{-- Empty state --}}
                    @if($columnTasks->isEmpty())
                        <div class="flex flex-col items-center justify-center py-10 rounded-xl border-2 border-dashed border-stone-100 text-stone-300 select-none">
                            <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-xs font-medium">No tasks</p>
                        </div>
                    @endif

                </div>
            </div>
        @endforeach

    </div>

    @assets
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    @endassets

    @script
    <script>
        function initBoard() {
            document.querySelectorAll('.task-list:not([data-sortable])').forEach(list => {
                list.dataset.sortable = '1';
                Sortable.create(list, {
                    group:      'board',
                    animation:  150,
                    ghostClass: 'opacity-30',
                    dragClass:  'task-card-dragging',
                    onEnd(evt) {
                        const taskId     = parseInt(evt.item.dataset.taskId);
                        const newStatus  = evt.to.dataset.status;
                        const orderedIds = Array.from(
                            evt.to.querySelectorAll('[data-task-id]')
                        ).map(el => parseInt(el.dataset.taskId));

                        $wire.taskMoved(taskId, newStatus, orderedIds);
                    }
                });
            });
        }

        initBoard();
    </script>
    @endscript
</div>
