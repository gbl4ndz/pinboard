<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">Dashboard</h2>
            <span class="text-sm text-stone-400">{{ now()->format('l, F j') }}</span>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Welcome banner --}}
            <div class="card p-8 relative overflow-hidden bg-gradient-to-r from-stone-900 to-stone-700">
                <div class="relative z-10">
                    <p class="text-stone-400 text-xs font-semibold uppercase tracking-widest mb-1">Workspace</p>
                    <h3 class="text-2xl font-bold text-white">
                        Welcome back, {{ Auth::user()->name }}
                    </h3>
                    <p class="text-stone-400 text-sm mt-1.5">
                        Manage your projects and tasks across your team.
                    </p>
                </div>
                {{-- Subtle grid pattern --}}
                <div class="absolute inset-0 opacity-5"
                     style="background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 24px 24px;"></div>
                {{-- Decorative shape --}}
                <div class="absolute -right-8 -top-8 w-40 h-40 rounded-full bg-white opacity-5"></div>
                <div class="absolute -right-4 -bottom-12 w-56 h-56 rounded-full bg-green-600 opacity-10"></div>
            </div>

            {{-- Quick actions --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                <a href="{{ route('projects.index') }}"
                   class="card p-6 hover:border-stone-300 hover:shadow-md transition group flex flex-col gap-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center group-hover:bg-blue-100 transition">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold text-stone-900 text-sm">Projects</div>
                        <div class="text-stone-400 text-xs mt-0.5">View and manage all projects</div>
                    </div>
                    <div class="flex items-center gap-1 text-xs text-stone-400 group-hover:text-green-700 transition mt-auto">
                        Open <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>

                <a href="{{ route('projects.index') }}"
                   class="card p-6 hover:border-stone-300 hover:shadow-md transition group flex flex-col gap-4">
                    <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center group-hover:bg-violet-100 transition">
                        <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold text-stone-900 text-sm">Kanban Board</div>
                        <div class="text-stone-400 text-xs mt-0.5">Open a project to view its board</div>
                    </div>
                    <div class="flex items-center gap-1 text-xs text-stone-400 group-hover:text-green-700 transition mt-auto">
                        Open <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>

                <a href="{{ route('public.board') }}" target="_blank"
                   class="card p-6 hover:border-stone-300 hover:shadow-md transition group flex flex-col gap-4">
                    <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center group-hover:bg-green-100 transition">
                        <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold text-stone-900 text-sm flex items-center gap-1.5">
                            Public Dashboard
                            <svg class="w-3 h-3 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </div>
                        <div class="text-stone-400 text-xs mt-0.5">Monitor-friendly live view</div>
                    </div>
                    <div class="flex items-center gap-1 text-xs text-stone-400 group-hover:text-green-700 transition mt-auto">
                        Open in new tab <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>

            </div>

        </div>
    </div>
</x-app-layout>
