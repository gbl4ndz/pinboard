<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('projects.show', $project) }}" class="text-stone-400 hover:text-stone-600 text-sm transition">← {{ $project->name }}</a>
            <span class="text-stone-300">/</span>
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">Edit Project</h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-stone-200 p-8">
                <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-6">
                    @csrf @method('PUT')

                    <div>
                        <label for="name" class="block text-sm font-medium text-stone-700 mb-1.5">Name</label>
                        <input id="name" name="name" type="text"
                               value="{{ old('name', $project->name) }}"
                               required autofocus
                               class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-2.5 text-stone-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition @error('name') border-red-300 @enderror">
                        @error('name')
                            <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-stone-700 mb-1.5">Description</label>
                        <textarea id="description" name="description" rows="4"
                                  class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-2.5 text-stone-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition @error('description') border-red-300 @enderror">{{ old('description', $project->description) }}</textarea>
                        @error('description')
                            <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <input id="is_public" name="is_public" type="checkbox" value="1"
                               {{ old('is_public', $project->is_public) ? 'checked' : '' }}
                               class="rounded border-stone-300 text-green-600 focus:ring-green-500">
                        <label for="is_public" class="text-sm text-stone-700">
                            Show on public Kanban board
                        </label>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                                class="bg-green-700 hover:bg-green-800 text-white font-medium px-6 py-2.5 rounded-xl transition">
                            Save Changes
                        </button>
                        <a href="{{ route('projects.show', $project) }}"
                           class="text-stone-500 hover:text-stone-700 text-sm transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
