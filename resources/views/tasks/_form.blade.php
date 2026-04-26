@php
    $statuses   = \App\Enums\TaskStatus::cases();
    $priorities = \App\Enums\TaskPriority::cases();
@endphp

<div class="space-y-5">

    {{-- Title --}}
    <div>
        <label for="title" class="form-label">Title</label>
        <input id="title" name="title" type="text"
               value="{{ old('title', $task->title ?? '') }}"
               required autofocus placeholder="What needs to be done?"
               class="form-input {{ $errors->has('title') ? 'form-input-error' : '' }}">
        @error('title')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    {{-- Description --}}
    <div>
        <label for="description" class="form-label">Description</label>
        <textarea id="description" name="description" rows="4"
                  placeholder="Add any relevant details or context…"
                  class="form-input resize-none {{ $errors->has('description') ? 'form-input-error' : '' }}">{{ old('description', $task->description ?? '') }}</textarea>
        @error('description')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

        {{-- Status --}}
        <div>
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-input">
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
            <label for="priority" class="form-label">Priority</label>
            <select id="priority" name="priority" class="form-input">
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
            <label for="due_date" class="form-label">Due date</label>
            <input id="due_date" name="due_date" type="date"
                   value="{{ old('due_date', isset($task) ? $task->due_date?->format('Y-m-d') : '') }}"
                   class="form-input">
        </div>

        {{-- Assignee (manager only) --}}
        @if(auth()->user()->hasPermissionTo('assign task'))
        <div>
            <label for="assigned_to" class="form-label">Assign to</label>
            <select id="assigned_to" name="assigned_to" class="form-input">
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
    <label class="flex items-center gap-3 cursor-pointer group w-fit">
        <input id="is_public" name="is_public" type="checkbox" value="1"
               {{ old('is_public', $task->is_public ?? false) ? 'checked' : '' }}
               class="w-4 h-4 rounded border-stone-300 text-green-600 focus:ring-green-500/30 transition">
        <span class="text-sm text-stone-700 group-hover:text-stone-900 transition-colors">
            Show on public Kanban board
        </span>
    </label>

</div>
