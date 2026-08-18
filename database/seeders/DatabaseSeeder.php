<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Website refresh', 'Operations', 'Personal'] as $name) {
            $project = Project::factory()->create(['name' => $name]);

            Task::factory(4)->sequence(
                ['priority' => 1],
                ['priority' => 2],
                ['priority' => 3],
                ['priority' => 4],
            )->for($project)->create();
        }

        Task::factory(2)->sequence(
            ['priority' => 1],
            ['priority' => 2],
        )->create();
    }
}
