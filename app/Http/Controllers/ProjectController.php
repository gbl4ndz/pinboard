<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Project::class);

        $projects = auth()->user()
            ->memberProjects()
            ->with('creator')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('projects.index', compact('projects'));
    }

    public function create(): View
    {
        $this->authorize('create', Project::class);

        return view('projects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Project::class);

        $data = $request->validate($this->projectRules());

        $data['created_by'] = $request->user()->id;
        $data['slug']       = $this->uniqueSlug($data['name']);
        $data['is_public']  = $request->boolean('is_public');

        $project = Project::create($data);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created.');
    }

    public function show(Project $project): View
    {
        $this->authorize('view', $project);

        $project->load(['members']);

        $nonMembers = User::whereNotIn('id', $project->members->pluck('id'))
            ->orderBy('name')
            ->get();

        return view('projects.show', compact('project', 'nonMembers'));
    }

    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $data = $request->validate($this->projectRules());

        if ($project->name !== $data['name']) {
            $data['slug'] = $this->uniqueSlug($data['name'], $project->id);
        }

        $data['is_public'] = $request->boolean('is_public');

        $project->update($data);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted.');
    }

    private function projectRules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'is_public'   => 'boolean',
        ];
    }

    private function uniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 2;

        while (
            Project::where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
