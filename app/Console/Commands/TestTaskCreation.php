<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;

class TestTaskCreation extends Command
{
    protected $signature = 'app:test-task';
    protected $description = 'Create a test task to test event system';

    public function handle()
    {
        $this->info('Creating test task...');

        $user = User::first();
        $project = Project::first();

        if (!$user) {
            $this->error('No users found. Please run seeders first.');
            return 1;
        }

        if (!$project) {
            $this->error('No projects found. Please run seeders first.');
            return 1;
        }

        $task = Task::create([
            'project_id' => $project->id,
            'author_id' => $user->id,
            'title' => 'Test Task for Queue System #' . time(),
            'description' => 'This is a test task to check queue functionality and events',
            'status' => 'todo',
            'priority' => 'high',
            'due_date' => now()->addDays(7)
        ]);

        $this->info("Task created successfully!");
        $this->table(['Field', 'Value'], [
            ['ID', $task->id],
            ['Title', $task->title],
            ['Project', $project->name],
            ['Author', $user->name],
            ['Status', $task->status],
            ['Priority', $task->priority],
        ]);

        $this->info('Check the logs to see if the notification job was queued and processed.');

        return 0;
    }
}
