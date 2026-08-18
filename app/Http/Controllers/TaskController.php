<?php

namespace App\Http\Controllers;

use App\Actions\TaskOrdering;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\TaskIndexRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(TaskIndexRequest $request): View
    {
        $projects = Project::query()->orderBy('name')->get();
        $requestedFilter = $request->validated('project');
        $selectedProject = is_numeric($requestedFilter)
            ? $projects->firstWhere('id', (int) $requestedFilter)
            : null;
        $filter = match (true) {
            $requestedFilter === 'unassigned' => 'unassigned',
            $selectedProject !== null => (string) $selectedProject->id,
            default => 'all',
        };

        $tasks = Task::query()
            ->with('project')
            ->when(
                $filter !== 'all' && $filter !== 'unassigned',
                fn ($query) => $query->where('project_id', (int) $filter)
            )
            ->when(
                $filter === 'unassigned',
                fn ($query) => $query->whereNull('project_id')
            )
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        $sections = $filter === 'all'
            ? $projects->map(fn (Project $project) => [
                'title' => $project->name,
                'project_id' => $project->id,
                'tasks' => $tasks->where('project_id', $project->id),
            ])->push([
                'title' => 'Unassigned',
                'project_id' => null,
                'tasks' => $tasks->whereNull('project_id'),
            ])
            : collect([[
                'title' => $filter === 'unassigned' ? 'Unassigned' : $selectedProject->name,
                'project_id' => $filter === 'unassigned' ? null : $selectedProject->id,
                'tasks' => $tasks,
            ]]);

        return view('tasks.index', [
            'filter' => $filter,
            'filterTitle' => $filter === 'all'
                ? 'All tasks'
                : ($filter === 'unassigned' ? 'Unassigned' : $selectedProject->name),
            'filterDescription' => $filter === 'all'
                ? 'Drag tasks within their section to change priority.'
                : 'Drag a task to change its priority.',
            'projects' => $projects,
            'sections' => $sections,
            'taskCount' => $tasks->count(),
        ]);
    }

    public function store(StoreTaskRequest $request, TaskOrdering $ordering): RedirectResponse
    {
        $ordering->create($request->validated());

        return back()->with('status', 'Task added.');
    }

    public function update(
        UpdateTaskRequest $request,
        Task $task,
        TaskOrdering $ordering
    ): RedirectResponse {
        $ordering->update($task, $request->validated());

        return back()->with('status', 'Task updated.');
    }

    public function destroy(Task $task, TaskOrdering $ordering): RedirectResponse
    {
        $ordering->delete($task);

        return back()->with('status', 'Task deleted.');
    }
}
