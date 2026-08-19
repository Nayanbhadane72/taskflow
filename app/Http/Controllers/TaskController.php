<?php

namespace App\Http\Controllers;

use App\Actions\TaskOrdering;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $projects = Project::query()->orderBy('name')->get();
        $requestedFilter = $request->query('project', 'all');
        $selectedProject = is_numeric($requestedFilter)
            ? $projects->firstWhere('id', (int) $requestedFilter)
            : null;

        if ($requestedFilter === 'unassigned') {
            $filter = 'unassigned';
            $sections = [$this->section('Unassigned', null)];
        } elseif ($selectedProject !== null) {
            $filter = (string) $selectedProject->id;
            $sections = [$this->section($selectedProject->name, $selectedProject->id)];
        } else {
            $filter = 'all';
            $sections = [];

            foreach ($projects as $project) {
                $sections[] = $this->section($project->name, $project->id);
            }

            $sections[] = $this->section('Unassigned', null);
        }

        return view('tasks.index', [
            'filter' => $filter,
            'projects' => $projects,
            'sections' => $sections,
        ]);
    }

    private function section(string $title, ?int $projectId): array
    {
        $tasks = Task::query()
            ->orderBy('priority')
            ->orderBy('id');

        if ($projectId === null) {
            $tasks->whereNull('project_id');
        } else {
            $tasks->where('project_id', $projectId);
        }

        return [
            'title' => $title,
            'project_id' => $projectId,
            'tasks' => $tasks->get(),
        ];
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
