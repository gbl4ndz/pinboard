<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('projects.show', $project) }}" class="text-stone-400 hover:text-stone-600 text-sm transition">← {{ $project->name }}</a>
            <span class="text-stone-300">/</span>
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">Edit Task</h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-stone-200 p-8">
                <form method="POST" action="{{ route('tasks.update', $task) }}">
                    @csrf @method('PUT')
                    @include('tasks._form')
                    <div class="flex items-center gap-3 pt-6">
                        <button type="submit"
                                class="bg-green-700 hover:bg-green-800 text-white font-medium px-6 py-2.5 rounded-xl transition">
                            Save Changes
                        </button>
                        <a href="{{ route('projects.show', $project) }}"
                           class="text-stone-500 hover:text-stone-700 text-sm transition">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
