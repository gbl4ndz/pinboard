<div class="px-4 sm:px-6 lg:px-8 py-6">

    {{-- Livewire loading bar --}}
    <div wire:loading.delay wire:target="taskMoved" class="loading-bar"></div>

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-2.5 min-w-0">
            <a href="{{ route('projects.show', $this->project) }}"
               class="inline-flex items-center gap-1.5 text-sm text-stone-400 hover:text-stone-700
                      transition shrink-0 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                {{ $this->project->name }}
            </a>
            <span class="text-stone-200 select-none">/</span>
            <h2 class="font-bold text-lg text-stone-900 truncate">Board</h2>
            @if($this->project->is_public)
                <a href="{{ route('public.board', ['slug' => $this->project->slug]) }}"
                   target="_blank"
                   class="btn btn-xs btn-secondary shrink-0">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Public view
                </a>
            @endif
        </div>

        <div class="flex items-center gap-3 shrink-0">
            {{-- Team avatars --}}
            @if($this->teamMembers->isNotEmpty())
                <div class="flex items-center gap-2">
                    <div class="flex -space-x-1.5">
                        @foreach($this->teamMembers->take(5) as $member)
                            @php
                                $colors = ['bg-violet-500','bg-blue-500','bg-emerald-500','bg-orange-400','bg-rose-500'];
                                $color  = $colors[$loop->index % count($colors)];
                            @endphp
                            <div class="w-7 h-7 rounded-full {{ $color }} ring-2 ring-white
                                        flex items-center justify-center text-[10px] font-bold text-white
                                        relative group cursor-default"
                                 title="{{ $member['user']->name }}">
                                {{ strtoupper(substr($member['user']->name, 0, 1)) }}
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2
                                            hidden group-hover:block pointer-events-none
                                            bg-stone-900 text-white text-xs rounded-lg
                                            px-2.5 py-1.5 whitespace-nowrap z-20 shadow-xl">
                                    <span class="font-medium">{{ $member['user']->name }}</span>
                                    <span class="text-stone-400 ml-1">{{ $member['count'] }} {{ Str::plural('task', $member['count']) }}</span>
                                </div>
                            </div>
                        @endforeach
                        @if($this->teamMembers->count() > 5)
                            <div class="w-7 h-7 rounded-full bg-stone-100 ring-2 ring-white
                                        flex items-center justify-center text-[10px] font-bold text-stone-500">
                                +{{ $this->teamMembers->count() - 5 }}
                            </div>
                        @endif
                    </div>
                    <span class="text-xs text-stone-400 font-medium">
                        {{ $this->teamMembers->count() }} {{ Str::plural('member', $this->teamMembers->count()) }}
                    </span>
                </div>
                <div class="w-px h-5 bg-stone-200"></div>
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
        <div class="bg-white rounded-xl border border-stone-200 shadow-sm px-5 py-3.5 mb-5
                    flex items-center gap-5 flex-wrap">

            {{-- Progress --}}
            <div class="flex-1 min-w-[180px]">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-semibold text-stone-500 uppercase tracking-wide">Progress</span>
                    <span class="text-xs font-bold tabular-nums
                        {{ $this->stats['percent'] === 100 ? 'text-green-600' : 'text-stone-700' }}">
                        {{ $this->stats['done'] }}/{{ $this->stats['total'] }}
                        <span class="text-stone-400 font-medium">&middot; {{ $this->stats['percent'] }}%</span>
                    </span>
                </div>
                <div class="h-1.5 bg-stone-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700
                        {{ $this->stats['percent'] === 100
                            ? 'bg-gradient-to-r from-green-400 to-green-500'
                            : 'bg-gradient-to-r from-green-500 to-emerald-500' }}"
                         style="width: {{ $this->stats['percent'] }}%">
                    </div>
                </div>
            </div>

            @if($this->stats['overdue'] > 0 || $this->stats['today'] > 0 || $this->stats['tomorrow'] > 0)
                <div class="hidden sm:block w-px h-6 bg-stone-100"></div>
            @endif

            {{-- Alert pills --}}
            <div class="flex items-center gap-2 flex-wrap shrink-0">
                @if($this->stats['overdue'] > 0)
                    <span class="stat-pill bg-red-50 text-red-600 border-red-200">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $this->stats['overdue'] }} overdue
                    </span>
                @endif
                @if($this->stats['today'] > 0)
                    <span class="stat-pill bg-orange-50 text-orange-600 border-orange-200">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $this->stats['today'] }} due today
                    </span>
                @endif
                @if($this->stats['tomorrow'] > 0)
                    <span class="stat-pill bg-amber-50 text-amber-600 border-amber-200">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ $this->stats['tomorrow'] }} due tomorrow
                    </span>
                @endif
                @if($this->stats['overdue'] === 0 && $this->stats['today'] === 0 && $this->stats['tomorrow'] === 0)
                    <span class="flex items-center gap-1.5 text-xs text-stone-400">
                        <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        All on track
                    </span>
                @endif
            </div>

        </div>
    @endif

    {{-- ── Kanban columns ───────────────────────────────────────────────────── --}}
    <div class="flex gap-4 overflow-x-auto pb-6 items-start scrollbar-thin"
         wire:ignore.self>

        @foreach($this->columns as $status)
            @php
                $columnTasks = $this->tasks
                    ->where('status', $status)
                    ->sortBy('sort_order')
                    ->values();

                [$dotColor, $colAccent, $colHeaderBg] = match($status->value) {
                    'done'        => ['bg-green-500',  'border-t-green-500',  'text-green-700'],
                    'in_progress' => ['bg-amber-400',  'border-t-amber-400',  'text-amber-700'],
                    'review'      => ['bg-orange-400', 'border-t-orange-400', 'text-orange-700'],
                    'todo'        => ['bg-blue-400',   'border-t-blue-400',   'text-blue-700'],
                    default       => ['bg-stone-300',  'border-t-stone-300',  'text-stone-500'],
                };

                $today    = now()->startOfDay();
                $tomorrow = now()->addDay()->startOfDay();
            @endphp

            <div class="flex-shrink-0 w-[272px] flex flex-col" wire:key="col-{{ $status->value }}">

                {{-- Column wrapper: subtle bg, top accent border --}}
                <div class="bg-stone-50/70 rounded-2xl border border-stone-200/80 border-t-2 {{ $colAccent }}
                            flex flex-col p-3 min-h-[400px]">

                    {{-- Column header --}}
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $dotColor }}"></span>
                            <h3 class="text-xs font-bold text-stone-700 uppercase tracking-wide">
                                {{ $status->label() }}
                            </h3>
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full
                                         bg-stone-200/80 text-[10px] font-bold text-stone-500 tabular-nums">
                                {{ $columnTasks->count() }}
                            </span>
                        </div>
                        @can('create', App\Models\Task::class)
                            <a href="{{ route('projects.tasks.create', $this->project) }}?status={{ $status->value }}"
                               class="w-6 h-6 rounded-md flex items-center justify-center
                                      text-stone-400 hover:text-stone-700 hover:bg-stone-200/60
                                      transition-colors duration-150"
                               title="Add task">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                </svg>
                            </a>
                        @endcan
                    </div>

                    {{-- Task list — IMPORTANT: task-list class and data-status must not change (used by SortableJS) --}}
                    <div class="task-list space-y-2 flex-1 min-h-16"
                         data-status="{{ $status->value }}">

                        @foreach($columnTasks as $task)
                            @php
                                $isOverdue    = $task->due_date && $task->due_date->lt($today) && $task->status !== \App\Enums\TaskStatus::Done;
                                $isDueToday   = $task->due_date && $task->due_date->isSameDay($today);
                                $isDueTomorrow = $task->due_date && $task->due_date->isSameDay($tomorrow);

                                $priorityBorder = match($task->priority->value) {
                                    'high'   => 'border-l-red-400',
                                    'medium' => 'border-l-amber-400',
                                    default  => 'border-l-stone-200',
                                };
                            @endphp

                            {{-- IMPORTANT: data-task-id must stay on this element (read by SortableJS onEnd) --}}
                            <div class="saas-task-card animate-slide-up border-l-2 {{ $priorityBorder }}
                                        {{ $isOverdue ? '!border-red-200 !bg-red-50/40' : '' }}"
                                 data-task-id="{{ $task->id }}"
                                 wire:key="task-{{ $task->id }}">

                                {{-- Top row: due badge --}}
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[10px] font-bold uppercase tracking-widest
                                            {{ $task->priority->value === 'high'   ? 'text-red-500' :
                                               ($task->priority->value === 'medium' ? 'text-amber-600' : 'text-stone-400') }}">
                                            {{ $task->priority->label() }}
                                        </span>
                                    </div>

                                    @if($isOverdue)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold
                                                     bg-red-100 text-red-600 rounded-md px-1.5 py-0.5 uppercase tracking-wide">
                                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            Overdue
                                        </span>
                                    @elseif($isDueToday)
                                        <span class="text-[10px] font-bold bg-orange-100 text-orange-600 rounded-md px-1.5 py-0.5 uppercase tracking-wide">
                                            Today
                                        </span>
                                    @elseif($isDueTomorrow)
                                        <span class="text-[10px] font-bold bg-amber-100 text-amber-600 rounded-md px-1.5 py-0.5 uppercase tracking-wide">
                                            Tomorrow
                                        </span>
                                    @endif
                                </div>

                                {{-- Title --}}
                                <a href="{{ route('tasks.show', $task) }}"
                                   class="block text-sm font-semibold text-stone-800 hover:text-green-700
                                          leading-snug mb-1 transition-colors duration-150"
                                   onclick="event.stopPropagation()">
                                    {{ $task->title }}
                                </a>

                                {{-- Description excerpt --}}
                                @if($task->description)
                                    <p class="text-[11px] text-stone-400 leading-relaxed mb-2.5 line-clamp-2">
                                        {{ $task->description }}
                                    </p>
                                @endif

                                {{-- Bottom row --}}
                                <div class="flex items-center justify-between gap-2 pt-2 border-t border-stone-100 mt-2">

                                    {{-- Assignee --}}
                                    @if($task->assignee)
                                        @php
                                            $aColors = ['bg-violet-500','bg-blue-500','bg-emerald-500','bg-orange-400','bg-rose-500'];
                                            $aColor  = $aColors[$task->assignee->id % count($aColors)];
                                        @endphp
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <div class="w-5 h-5 rounded-full {{ $aColor }}
                                                        flex items-center justify-center text-[9px] font-bold text-white shrink-0"
                                                 title="{{ $task->assignee->name }}">
                                                {{ strtoupper(substr($task->assignee->name, 0, 1)) }}
                                            </div>
                                            <span class="text-[11px] text-stone-500 truncate font-medium">
                                                {{ $task->assignee->name }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-1">
                                            <div class="w-5 h-5 rounded-full border border-dashed border-stone-200
                                                        flex items-center justify-center shrink-0">
                                                <svg class="w-2.5 h-2.5 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                            </div>
                                            <span class="text-[11px] text-stone-300">Unassigned</span>
                                        </div>
                                    @endif

                                    {{-- Meta icons --}}
                                    <div class="flex items-center gap-2 text-stone-400 shrink-0">
                                        @if($task->comments_count > 0)
                                            <span class="flex items-center gap-0.5 text-[11px] font-medium">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                                </svg>
                                                {{ $task->comments_count }}
                                            </span>
                                        @endif
                                        @if($task->due_date && !$isOverdue && !$isDueToday && !$isDueTomorrow)
                                            <span class="flex items-center gap-0.5 text-[11px] font-medium">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <div class="flex flex-col items-center justify-center py-8 rounded-xl
                                        border-2 border-dashed border-stone-200/60 text-stone-300 select-none
                                        transition-colors duration-200 group-[.sortable-over]:border-green-300">
                                <svg class="w-6 h-6 mb-1.5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-xs font-medium">Drop tasks here</p>
                            </div>
                        @endif

                    </div>
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
                    ghostClass: 'opacity-25',
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
