<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Console\Command;

class TestCommentCreate extends Command
{
    protected $signature = 'app:test-comment-create {taskId=1} {--body=}';
    protected $description = 'Test comment creation for real-time broadcasting';

    public function handle()
    {
        $taskId = $this->argument('taskId');
        $body = $this->option('body') ?: 'Test comment created at ' . now()->toTimeString();

        $task = Task::find($taskId);
        if (!$task) {
            $this->error("Task with ID {$taskId} not found.");
            return 1;
        }

        $user = User::first();
        if (!$user) {
            $this->error("No user found. Please run seeders first.");
            return 1;
        }

        $comment = Comment::create([
            'task_id' => $taskId,
            'author_id' => $user->id,
            'body' => $body,
        ]);

        $this->info("✅ Comment created for task '{$task->title}'");
        $this->info("💭 Comment: {$body}");
        $this->info("🔔 Real-time event should be broadcast to project.{$task->project_id} channel");
        
        return 0;
    }
}