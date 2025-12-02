<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;

class CreateTestTasks extends Command
{
    protected $signature = 'app:create-test-tasks';
    protected $description = 'Create test tasks with different statuses';

    public function handle()
    {
        $this->info('Creating test tasks with different statuses...');

        $user = User::first();
        $project = Project::first();
        $project2 = Project::skip(1)->first();

        if (!$user || !$project || !$project2) {
            $this->error('Missing users or projects. Please run seeders first.');
            return 1;
        }

        $tasks = [
            [
                'title' => 'In Progress Task #' . time(),
                'status' => 'in_progress',
                'project_id' => $project->id,
                'priority' => 'medium'
            ],
            [
                'title' => 'Completed Task #' . time(),
                'status' => 'done',
                'project_id' => $project->id,
                'priority' => 'low'
            ],
            [
                'title' => 'Expired Task #' . time(),
                'status' => 'expired',
                'project_id' => $project2->id,
                'priority' => 'high'
            ],
            [
                'title' => 'Another TODO Task #' . time(),
                'status' => 'todo',
                'project_id' => $project2->id,
                'priority' => 'medium'
            ]
        ];

        foreach ($tasks as $taskData) {
            $task = Task::create([
                'project_id' => $taskData['project_id'],
                'author_id' => $user->id,
                'title' => $taskData['title'],
                'description' => 'Test task with ' . $taskData['status'] . ' status',
                'status' => $taskData['status'],
                'priority' => $taskData['priority'],
                'due_date' => now()->addDays(rand(1, 10))
            ]);

            $this->info("Created task: {$task->title} ({$task->status})");
        }

        $this->info('Test tasks created successfully!');
        return 0;
    }
}