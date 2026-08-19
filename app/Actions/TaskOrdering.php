<?php

namespace App\Actions;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
            $oldProjectId = $task->project_id;

            $task->fill($attributes);

            if ($oldProjectId !== $task->project_id) {
                $task->priority = $this->nextPriority($task->project_id);
            }

            $task->save();

            if ($oldProjectId !== $task->project_id) {
                $this->resequence($oldProjectId);
                $this->resequence($task->project_id);
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
            $tasks = $this->scope($projectId)->get();
            $priority = 1;

            foreach ($taskIds as $taskId) {
                $task = $tasks->firstWhere('id', $taskId);

                if ($task !== null) {
                    $task->update(['priority' => $priority]);
                    $priority++;
                }
            }

            $this->resequence($projectId);
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
        return $projectId === null
            ? Task::whereNull('project_id')
            : Task::where('project_id', $projectId);
    }
}
