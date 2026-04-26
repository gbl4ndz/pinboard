<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-base text-stone-800">Dashboard</h2>
            <span class="text-xs font-medium text-stone-400">{{ now()->format('l, F j') }}</span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Welcome banner --}}
            <div class="relative overflow-hidden rounded-2xl bg-stone-900 px-8 py-8 shadow-md">
                {{-- Dot grid --}}
                <div class="absolute inset-0 opacity-[0.04]"
                     style="background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 22px 22px;"></div>
                {{-- Glow blobs --}}
                <div class="absolute -right-10 -top-10 w-48 h-48 rounded-full bg-green-500 opacity-10 blur-2xl pointer-events-none"></div>
                <div class="absolute right-24 -bottom-8 w-40 h-40 rounded-full bg-emerald-400 opacity-10 blur-3xl pointer-events-none"></div>

                <div class="relative z-10">
                    <p class="text-stone-500 text-[11px] font-bold uppercase tracking-widest mb-1.5">Workspace</p>
                    <h3 class="text-2xl font-bold text-white tracking-tight">
                        Welcome back, {{ Auth::user()->name }}
                    </h3>
                    <p class="text-stone-400 text-sm mt-1.5 max-w-md">
                        Manage projects, track tasks, and keep your team aligned in one place.
                    </p>

                    <div class="mt-5 flex items-center gap-3">
                        <a href="{{ route('projects.index') }}" class="btn btn-sm bg-white text-stone-900 hover:bg-stone-100 shadow-none border-0 font-semibold">
                            View Projects
                        </a>
                        <a href="{{ route('public.board') }}" target="_blank"
                           class="btn btn-sm text-stone-400 hover:text-white hover:bg-white/10 border border-white/10">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Public Monitor
                        </a>
                    </div>
                </div>
            </div>

            {{-- Quick actions --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                <a href="{{ route('projects.index') }}"
                   class="card-interactive p-5 group flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center
                                group-hover:bg-blue-100 transition-colors duration-200 shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-stone-900 text-sm">Projects</div>
                        <div class="text-stone-400 text-xs mt-0.5 leading-relaxed">
                            View and manage all your projects
                        </div>
                        <div class="flex items-center gap-1 text-xs text-stone-400
                                    group-hover:text-green-600 transition-colors duration-150 mt-3 font-medium">
                            Open
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </a>

                <a href="{{ route('projects.index') }}"
                   class="card-interactive p-5 group flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center
                                group-hover:bg-violet-100 transition-colors duration-200 shrink-0">
                        <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-stone-900 text-sm">Kanban Board</div>
                        <div class="text-stone-400 text-xs mt-0.5 leading-relaxed">
                            Open a project to view its board
                        </div>
                        <div class="flex items-center gap-1 text-xs text-stone-400
                                    group-hover:text-green-600 transition-colors duration-150 mt-3 font-medium">
                            Open
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </a>

                <a href="{{ route('public.board') }}" target="_blank"
                   class="card-interactive p-5 group flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center
                                group-hover:bg-green-100 transition-colors duration-200 shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-stone-900 text-sm flex items-center gap-1.5">
                            Public Dashboard
                            <svg class="w-3 h-3 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </div>
                        <div class="text-stone-400 text-xs mt-0.5 leading-relaxed">
                            Monitor-friendly live Kanban view
                        </div>
                        <div class="flex items-center gap-1 text-xs text-stone-400
                                    group-hover:text-green-600 transition-colors duration-150 mt-3 font-medium">
                            Open in new tab
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </a>

            </div>

        </div>
    </div>
</x-app-layout>
