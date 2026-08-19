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
            $project = Project::firstOrCreate(['name' => $name]);

            foreach ($taskNames as $priority => $taskName) {
                Task::firstOrCreate(
                    [
                        'name' => $taskName,
                        'project_id' => $project->id,
                    ],
                    ['priority' => $priority + 1]
                );
            }
        }

        foreach (['Buy batteries', 'Reply to Mum'] as $priority => $taskName) {
            Task::firstOrCreate(
                ['name' => $taskName, 'project_id' => null],
                ['priority' => $priority + 1]
            );
        }
    }
}
