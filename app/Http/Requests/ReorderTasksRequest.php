<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderTasksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')],
            'task_ids' => ['required', 'array'],
            'task_ids.*' => ['required', 'integer', 'distinct'],
        ];
    }
}
