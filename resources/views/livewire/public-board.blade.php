<div class="min-h-screen flex flex-col bg-slate-50" wire:poll.30s>

    {{-- ── Top bar ──────────────────────────────────────────────────────────── --}}
    <header class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between gap-6 shrink-0">

        {{-- Brand --}}
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-9 h-9 rounded-xl bg-green-700 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                          d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-0.5">
                    {{ config('app.name') }}
                </div>
                @if($this->project)
                    <div class="text-lg font-bold text-slate-900 truncate leading-tight">
                        {{ $this->project->name }}
                    </div>
                @else
                    <div class="text-lg font-bold text-slate-400 leading-tight">No project selected</div>
                @endif
            </div>
        </div>

        {{-- Project selector (multiple public projects) --}}
        @if($this->publicProjects->count() > 1)
            <div class="flex items-center gap-2 flex-wrap justify-center">
                @foreach($this->publicProjects as $p)
                    <button wire:click="selectProject({{ $p->id }})"
                            class="text-sm px-4 py-1.5 rounded-lg border font-medium transition
                                   {{ $this->projectId === $p->id
                                       ? 'bg-slate-900 text-white border-slate-900'
                                       : 'bg-white text-slate-500 border-slate-200 hover:border-slate-400 hover:text-slate-800' }}">
                        {{ $p->name }}
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Time + Live indicator --}}
        <div class="flex items-center gap-5 shrink-0">
            <div class="text-right hidden md:block">
                <div class="text-2xl font-bold tabular-nums text-slate-900 leading-none tracking-tight">
                    {{ now()->format('H:i') }}
                </div>
                <div class="text-xs text-slate-400 mt-0.5 tracking-wide">{{ now()->format('D, M j') }}</div>
            </div>
            <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-1.5">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-bold text-emerald-700 uppercase tracking-widest">Live</span>
            </div>
        </div>
    </header>

    @if(!$this->project)
        {{-- No public projects empty state --}}
        <div class="flex-1 flex items-center justify-center">
            <div class="text-center">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                </div>
                <p class="text-slate-600 text-xl font-semibold">No public projects</p>
                <p class="text-slate-400 text-sm mt-1.5">Projects marked as public will appear here</p>
            </div>
        </div>
    @else

        {{-- ── Metrics strip ─────────────────────────────────────────────────── --}}
        <div class="bg-white border-b border-slate-100 px-8 py-4 shrink-0">
            <div class="flex gap-8 overflow-x-auto items-center">
                @foreach($this->metrics as $m)
                    @php
                        [$dotColor, $numColor] = match($m['status']->value) {
                            'done'        => ['bg-emerald-500', 'text-emerald-700'],
                            'in_progress' => ['bg-yellow-400',  'text-yellow-700'],
                            'review'      => ['bg-orange-400',  'text-orange-700'],
                            'todo'        => ['bg-blue-400',    'text-blue-700'],
                            default       => ['bg-slate-300',   'text-slate-600'],
                        };
                    @endphp
                    <div class="flex items-center gap-3 min-w-[120px]">
                        <span class="w-3 h-3 rounded-full {{ $dotColor }} shrink-0"></span>
                        <div>
                            <div class="text-3xl font-bold tabular-nums leading-none {{ $numColor }}">
                                {{ $m['count'] }}
                            </div>
                            <div class="text-xs font-semibold text-slate-400 mt-1 uppercase tracking-widest">
                                {{ $m['status']->publicLabel() }}
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="flex items-center gap-3 min-w-[120px] border-l border-slate-100 pl-8 ml-auto">
                    <div class="w-3 h-3 rounded-full bg-slate-200 shrink-0"></div>
                    <div>
                        <div class="text-3xl font-bold tabular-nums text-slate-700 leading-none">
                            {{ $this->tasks->count() }}
                        </div>
                        <div class="text-xs font-semibold text-slate-400 mt-1 uppercase tracking-widest">
                            Total
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Kanban columns ─────────────────────────────────────────────────── --}}
        <div class="flex-1 overflow-x-auto p-8">
            <div class="flex gap-6 h-full items-start" style="min-width: max-content;">

                @foreach($this->columns as $status)
                    @php
                        $columnTasks = $this->tasks
                            ->where('status', $status)
                            ->sortBy('sort_order')
                            ->values();

                        [$dotColor, $accentBg] = match($status->value) {
                            'done'        => ['bg-emerald-500', 'bg-emerald-500'],
                            'in_progress' => ['bg-yellow-400',  'bg-yellow-400'],
                            'review'      => ['bg-orange-400',  'bg-orange-400'],
                            'todo'        => ['bg-blue-400',    'bg-blue-400'],
                            default       => ['bg-slate-300',   'bg-slate-300'],
                        };
                    @endphp

                    <div class="w-80 flex flex-col" wire:key="pub-col-{{ $status->value }}">

                        {{-- Column header --}}
                        <div class="flex items-center justify-between mb-4 px-0.5">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full {{ $dotColor }} shrink-0"></span>
                                <h3 class="text-base font-bold text-slate-800">
                                    {{ $status->publicLabel() }}
                                </h3>
                                <span class="text-sm font-semibold text-slate-400 tabular-nums">
                                    ({{ $columnTasks->count() }})
                                </span>
                            </div>
                        </div>

                        {{-- Task cards --}}
                        <div class="space-y-3">
                            @forelse($columnTasks as $task)
                                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm"
                                     wire:key="pub-task-{{ $task->id }}">

                                    {{-- Priority --}}
                                    <div class="flex items-center gap-1.5 mb-2.5">
                                        <span class="w-2 h-2 rounded-full
                                            {{ $task->priority->value === 'high'   ? 'bg-red-400' :
                                               ($task->priority->value === 'medium' ? 'bg-amber-400' : 'bg-slate-300') }}">
                                        </span>
                                        <span class="text-[11px] font-bold uppercase tracking-wide
                                            {{ $task->priority->value === 'high'   ? 'text-red-500' :
                                               ($task->priority->value === 'medium' ? 'text-amber-600' : 'text-slate-400') }}">
                                            {{ $task->priority->label() }}
                                        </span>
                                    </div>

                                    {{-- Title --}}
                                    <p class="font-bold text-slate-900 leading-snug text-base">
                                        {{ $task->title }}
                                    </p>

                                    @if($task->description)
                                        <p class="text-sm text-slate-500 mt-2 leading-relaxed line-clamp-2">
                                            {{ $task->description }}
                                        </p>
                                    @endif

                                    {{-- Footer --}}
                                    @if($task->due_date)
                                        <div class="flex items-center gap-1.5 mt-3 pt-3 border-t border-slate-50">
                                            <svg class="w-3.5 h-3.5 {{ $task->due_date->isPast() ? 'text-red-400' : 'text-slate-300' }}"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="text-xs font-medium {{ $task->due_date->isPast() ? 'text-red-500' : 'text-slate-400' }}">
                                                {{ $task->due_date->format('M j, Y') }}
                                            </span>
                                        </div>
                                    @endif

                                </div>
                            @empty
                                <div class="flex flex-col items-center justify-center py-12 rounded-xl border-2 border-dashed border-slate-200 text-slate-300 select-none">
                                    <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <p class="text-sm font-medium">No tasks</p>
                                </div>
                            @endforelse
                        </div>

                    </div>
                @endforeach

            </div>
        </div>

        {{-- ── Footer ──────────────────────────────────────────────────────────── --}}
        <footer class="shrink-0 border-t border-slate-100 bg-white px-8 py-3 flex items-center justify-between">
            <span class="text-xs text-slate-400 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Showing public tasks only
            </span>
            <span class="text-xs text-slate-400">Auto-refreshes every 30 s</span>
        </footer>

    @endif

</div>
