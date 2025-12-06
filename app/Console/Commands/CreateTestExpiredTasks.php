<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CreateTestExpiredTasks extends Command
{
    protected $signature = 'app:create-test-expired-tasks';
    protected $description = 'Create test tasks that are older than 7 days for testing expired functionality';

    public function handle()
    {
        // Get first project and user
        $project = Project::first();
        $user = User::first();

        if (!$project || !$user) {
            $this->error('No project or user found. Please run seeders first.');
            return 1;
        }

        // Create tasks that are 8, 9, and 10 days old
        $tasksData = [
            [
                'title' => 'Old Task 1 - Should Expire',
                'description' => 'This task is 8 days old and should be marked as expired',
                'days_old' => 8
            ],
            [
                'title' => 'Old Task 2 - Should Expire',
                'description' => 'This task is 9 days old and should be marked as expired',
                'days_old' => 9
            ],
            [
                'title' => 'Old Task 3 - Should Expire', 
                'description' => 'This task is 10 days old and should be marked as expired',
                'days_old' => 10
            ],
            [
                'title' => 'Recent Task - Should NOT Expire',
                'description' => 'This task is only 5 days old and should not be marked as expired',
                'days_old' => 5
            ]
        ];

        foreach ($tasksData as $taskData) {
            $task = Task::create([
                'project_id' => $project->id,
                'author_id' => $user->id,
                'assignee_id' => $user->id,
                'title' => $taskData['title'],
                'description' => $taskData['description'],
                'status' => 'in_progress',
                'priority' => 'medium'
            ]);

            // Update the created_at timestamp to simulate old tasks
            $oldDate = Carbon::now()->subDays($taskData['days_old']);
            $task->created_at = $oldDate;
            $task->updated_at = $oldDate;
            $task->save();

            $this->info("Created task: {$task->title} ({$taskData['days_old']} days old)");
        }

        $this->info('');
        $this->info('Test tasks created successfully!');
        $this->info('Now run: php artisan app:check-expired-tasks');
        $this->info('to see the expiration functionality in action.');

        return 0;
    }
}