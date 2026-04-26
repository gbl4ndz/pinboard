<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-base text-stone-800">Projects</h2>
            @can('create', App\Models\Project::class)
                <a href="{{ route('projects.create') }}" class="btn btn-sm btn-primary">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Project
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if(session('success'))
                <div class="alert-success animate-slide-up">
                    <svg class="w-4 h-4 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @forelse($projects as $project)
                <div class="card px-5 py-4 flex items-center justify-between gap-4
                            hover:border-stone-300 hover:shadow-md transition-all duration-200 animate-fade-in group">

                    {{-- Icon + name --}}
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-stone-100 group-hover:bg-stone-200/60
                                    flex items-center justify-center shrink-0 transition-colors duration-200">
                            <svg class="w-[18px] h-[18px] text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('projects.show', $project) }}"
                                   class="font-semibold text-stone-900 hover:text-green-700 transition-colors text-sm truncate">
                                    {{ $project->name }}
                                </a>
                                @if($project->is_public)
                                    <span class="badge badge-green">Public</span>
                                @else
                                    <span class="badge badge-stone">Internal</span>
                                @endif
                            </div>
                            @if($project->description)
                                <p class="text-stone-400 text-xs mt-0.5 line-clamp-1">{{ $project->description }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Right side --}}
                    <div class="flex items-center gap-4 shrink-0">
                        <span class="text-xs text-stone-400 hidden lg:block">
                            {{ $project->creator->name }} &middot; {{ $project->created_at->diffForHumans() }}
                        </span>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('projects.board', $project) }}" class="btn btn-sm btn-primary">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                </svg>
                                Board
                            </a>
                            @can('update', $project)
                                <a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-secondary">Edit</a>
                            @endcan
                            @can('delete', $project)
                                <form method="POST" action="{{ route('projects.destroy', $project) }}"
                                      onsubmit="return confirm('Delete this project?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="card p-16 text-center animate-fade-in">
                    <div class="w-14 h-14 rounded-2xl bg-stone-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                        </svg>
                    </div>
                    <p class="text-stone-800 font-semibold text-sm">No projects yet</p>
                    <p class="text-stone-400 text-xs mt-1">Create your first project to get started.</p>
                    @can('create', App\Models\Project::class)
                        <a href="{{ route('projects.create') }}" class="btn btn-sm btn-primary mt-5 mx-auto">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create Project
                        </a>
                    @endcan
                </div>
            @endforelse

            <div>{{ $projects->links() }}</div>

        </div>
    </div>
</x-app-layout>
