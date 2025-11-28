<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = [
            [
                'project_id' => 1,
                'author_id' => 1,
                'assignee_id' => 2,
                'title' => 'Set up database schema',
                'description' => 'Design and migrate initial schema for TaskFlow+',
                'status' => 'in_progress',
                'priority' => 'high',
                'due_date' => now()->addDays(3),
            ],
            [
                'project_id' => 1,
                'author_id' => 1,
                'assignee_id' => 3,
                'title' => 'Implement auth',
                'description' => 'Login, registration, password reset',
                'status' => 'todo',
                'priority' => 'medium',
                'due_date' => now()->addDays(5),
            ],
            [
                'project_id' => 2,
                'author_id' => 2,
                'assignee_id' => 4,
                'title' => 'Create mobile UI mockups',
                'description' => null,
                'status' => 'in_progress',
                'priority' => 'high',
                'due_date' => now()->addDays(7),
            ],
            [
                'project_id' => 3,
                'author_id' => 3,
                'assignee_id' => 5,
                'title' => 'Landing page content',
                'description' => 'Write marketing copy for homepage',
                'status' => 'todo',
                'priority' => 'low',
                'due_date' => null,
            ],
            [
                'project_id' => 4,
                'author_id' => 1,
                'assignee_id' => null,
                'title' => 'CI/CD pipeline',
                'description' => 'Basic GitHub Actions pipeline',
                'status' => 'todo',
                'priority' => 'medium',
                'due_date' => now()->addDays(10),
            ],
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }
    }
}
