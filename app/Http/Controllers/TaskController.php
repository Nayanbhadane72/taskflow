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
        $filter = $request->validated('project');
        $tasks = Task::query()
            ->with('project')
            ->when(
                is_numeric($filter),
                fn ($query) => $query->where('project_id', (int) $filter)
            )
            ->when(
                $filter === 'unassigned',
                fn ($query) => $query->whereNull('project_id')
            )
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        return view('tasks.index', [
            'tasks' => $tasks,
            'projects' => Project::query()->orderBy('name')->get(),
            'filter' => $filter ?? 'all',
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
