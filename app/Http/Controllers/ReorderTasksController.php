<?php

namespace App\Http\Controllers;

use App\Actions\TaskOrdering;
use App\Http\Requests\ReorderTasksRequest;
use Illuminate\Http\JsonResponse;

class ReorderTasksController extends Controller
{
    public function __invoke(ReorderTasksRequest $request, TaskOrdering $ordering): JsonResponse
    {
        $ordering->reorder($request->validated('project_id'), $request->validated('task_ids'));

        return response()->json(['message' => 'Task order saved.']);
    }
}
