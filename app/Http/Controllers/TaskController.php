<?php

namespace App\Http\Controllers;

use App\Actions\TaskOrdering;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(): View
    {
        $projects = Project::query()->orderBy('name')->get();
        $requestedFilter = request()->query('project');
        $filter = 'all';
        $selectedProject = null;

        if ($requestedFilter === 'unassigned') {
            $filter = 'unassigned';
        } elseif (is_numeric($requestedFilter)) {
            $selectedProject = $projects->firstWhere('id', (int) $requestedFilter);

            if ($selectedProject !== null) {
                $filter = (string) $selectedProject->id;
            }
        }

        $tasksQuery = Task::query()
            ->orderBy('priority')
            ->orderBy('id');

        if ($filter === 'unassigned') {
            $tasksQuery->whereNull('project_id');
        } elseif ($filter !== 'all') {
            $tasksQuery->where('project_id', (int) $filter);
        }

        $tasks = $tasksQuery->get();
        $sections = [];

        if ($filter === 'all') {
            foreach ($projects as $project) {
                $sections[] = [
                    'title' => $project->name,
                    'project_id' => $project->id,
                    'tasks' => $tasks->where('project_id', $project->id),
                ];
            }

            $sections[] = [
                'title' => 'Unassigned',
                'project_id' => null,
                'tasks' => $tasks->whereNull('project_id'),
            ];
        } else {
            $sectionTitle = 'Unassigned';
            $sectionProjectId = null;

            if ($filter !== 'unassigned') {
                $sectionTitle = $selectedProject->name;
                $sectionProjectId = $selectedProject->id;
            }

            $sections[] = [
                'title' => $sectionTitle,
                'project_id' => $sectionProjectId,
                'tasks' => $tasks,
            ];
        }

        return view('tasks.index', [
            'filter' => $filter,
            'projects' => $projects,
            'sections' => $sections,
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
