<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project' => ['nullable', 'string'],
        ];
    }
}
