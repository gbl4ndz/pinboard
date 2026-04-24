@php
    $statuses   = \App\Enums\TaskStatus::cases();
    $priorities = \App\Enums\TaskPriority::cases();
@endphp

<div class="space-y-6">

    {{-- Title --}}
    <div>
        <label for="title" class="block text-sm font-medium text-stone-700 mb-1.5">Title</label>
        <input id="title" name="title" type="text"
               value="{{ old('title', $task->title ?? '') }}"
               required autofocus
               class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-2.5 text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition @error('title') border-red-300 @enderror">
        @error('title') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    {{-- Description --}}
    <div>
        <label for="description" class="block text-sm font-medium text-stone-700 mb-1.5">Description</label>
        <textarea id="description" name="description" rows="4"
                  class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-2.5 text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition @error('description') border-red-300 @enderror"
                  placeholder="Optional details…">{{ old('description', $task->description ?? '') }}</textarea>
        @error('description') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

        {{-- Status --}}
        <div>
            <label for="status" class="block text-sm font-medium text-stone-700 mb-1.5">Status</label>
            <select id="status" name="status"
                    class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-2.5 text-stone-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                @foreach($statuses as $s)
                    <option value="{{ $s->value }}"
                        {{ old('status', ($task->status ?? \App\Enums\TaskStatus::Backlog)->value) === $s->value ? 'selected' : '' }}>
                        {{ $s->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Priority --}}
        <div>
            <label for="priority" class="block text-sm font-medium text-stone-700 mb-1.5">Priority</label>
            <select id="priority" name="priority"
                    class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-2.5 text-stone-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                @foreach($priorities as $p)
                    <option value="{{ $p->value }}"
                        {{ old('priority', ($task->priority ?? \App\Enums\TaskPriority::Medium)->value) === $p->value ? 'selected' : '' }}>
                        {{ $p->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Due date --}}
        <div>
            <label for="due_date" class="block text-sm font-medium text-stone-700 mb-1.5">Due date</label>
            <input id="due_date" name="due_date" type="date"
                   value="{{ old('due_date', isset($task) ? $task->due_date?->format('Y-m-d') : '') }}"
                   class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-2.5 text-stone-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
        </div>

        {{-- Assignee (manager only) --}}
        @if(auth()->user()->hasPermissionTo('assign task'))
        <div>
            <label for="assigned_to" class="block text-sm font-medium text-stone-700 mb-1.5">Assign to</label>
            <select id="assigned_to" name="assigned_to"
                    class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-2.5 text-stone-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                <option value="">— Unassigned —</option>
                @foreach($assignableUsers as $u)
                    <option value="{{ $u->id }}"
                        {{ old('assigned_to', $task->assigned_to ?? '') == $u->id ? 'selected' : '' }}>
                        {{ $u->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

    </div>

    {{-- Public flag --}}
    <div class="flex items-center gap-3">
        <input id="is_public" name="is_public" type="checkbox" value="1"
               {{ old('is_public', $task->is_public ?? false) ? 'checked' : '' }}
               class="rounded border-stone-300 text-green-600 focus:ring-green-500">
        <label for="is_public" class="text-sm text-stone-700">Show on public Kanban board</label>
    </div>

</div>
