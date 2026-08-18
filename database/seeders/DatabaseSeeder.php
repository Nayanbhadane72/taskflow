<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            'Website cleanup' => [
                'Fix login redirect',
                'Check the mobile menu on Safari',
                'Send the homepage copy to Maya',
                'Remove the old contact form',
            ],
            'Office admin' => [
                'Call the printer about the invoice',
                'Order more envelopes',
                'Book the room for Friday',
                'Follow up on the expense report',
            ],
            'Things at home' => [
                'Book dentist appointment',
                'Replace the hallway light bulb',
                'Pick up dry cleaning',
                'Water the plants',
            ],
        ];

        foreach ($projects as $name => $taskNames) {
            $project = Project::create(['name' => $name]);

            foreach ($taskNames as $priority => $taskName) {
                Task::create([
                    'name' => $taskName,
                    'priority' => $priority + 1,
                    'project_id' => $project->id,
                ]);
            }
        }

        foreach (['Buy batteries', 'Reply to Mum'] as $priority => $taskName) {
            Task::create([
                'name' => $taskName,
                'priority' => $priority + 1,
            ]);
        }
    }
}
