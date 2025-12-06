<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;

class TestTaskUpdate extends Command
{
    protected $signature = 'app:test-task-update {taskId=1} {status=completed}';
    protected $description = 'Test task update by changing a task status';

    public function handle()
    {
        $taskId = $this->argument('taskId');
        $status = $this->argument('status');

        $task = Task::find($taskId);

        if (!$task) {
            $this->error("Task with ID {$taskId} not found.");
            return 1;
        }

        $oldStatus = $task->status;
        $task->status = $status;
        $task->save();

        $this->info("✅ Task ID {$taskId} status updated from '{$oldStatus}' to '{$status}'");
        $this->info("🔔 Real-time event should be broadcast to project.{$task->project_id} channel");
        
        return 0;
    }
}