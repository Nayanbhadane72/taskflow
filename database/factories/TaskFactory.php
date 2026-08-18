<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(4),
            'priority' => 1,
            'project_id' => null,
        ];
    }
}
