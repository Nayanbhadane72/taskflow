<?php

namespace App\Actions;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskOrdering
{
    public function create(array $attributes): Task
    {
        return DB::transaction(function () use ($attributes) {
            $attributes['priority'] = $this->nextPriority($attributes['project_id'] ?? null);

            return Task::create($attributes);
        });
    }

    public function update(Task $task, array $attributes): Task
    {
        return DB::transaction(function () use ($task, $attributes) {
            $oldProjectId = $task->project_id === null ? null : (int) $task->project_id;
            $newProjectId = isset($attributes['project_id']) && $attributes['project_id'] !== null
                ? (int) $attributes['project_id']
                : null;
            $attributes['project_id'] = $newProjectId;

            $task->fill($attributes);

            if ($oldProjectId !== $newProjectId) {
                $task->priority = $this->nextPriority($newProjectId);
            }

            $task->save();

            if ($oldProjectId !== $newProjectId) {
                $this->resequence($oldProjectId);
                $this->resequence($newProjectId);
            }

            return $task;
        });
    }

    public function delete(Task $task): void
    {
        DB::transaction(function () use ($task) {
            $projectId = $task->project_id;
            $task->delete();
            $this->resequence($projectId);
        });
    }

    public function reorder(?int $projectId, array $taskIds): void
    {
        DB::transaction(function () use ($projectId, $taskIds) {
            $tasks = $this->scope($projectId)->orderBy('priority')->get();
            $expected = $tasks->modelKeys();
            $submitted = array_values(array_map('intval', $taskIds));
            sort($expected);
            $sortedSubmitted = $submitted;
            sort($sortedSubmitted);

            if ($expected !== $sortedSubmitted) {
                throw ValidationException::withMessages([
                    'task_ids' => 'The task list is out of date. Refresh and try again.',
                ]);
            }

            $byId = $tasks->keyBy('id');

            foreach ($submitted as $priority => $taskId) {
                $byId->get($taskId)->update(['priority' => $priority + 1]);
            }
        });
    }

    private function nextPriority(?int $projectId): int
    {
        return (int) ($this->scope($projectId)->max('priority') ?? 0) + 1;
    }

    private function resequence(?int $projectId): void
    {
        $this->scope($projectId)
            ->orderBy('priority')
            ->orderBy('id')
            ->get()
            ->each(function (Task $task, int $index) {
                $task->update(['priority' => $index + 1]);
            });
    }

    private function scope(?int $projectId): Builder
    {
        return Task::query()
            ->when(
                $projectId === null,
                fn (Builder $query) => $query->whereNull('project_id'),
                fn (Builder $query) => $query->where('project_id', $projectId)
            );
    }
}
