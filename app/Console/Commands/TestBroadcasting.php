<?php

namespace App\Console\Commands;

use App\Events\TaskUpdated;
use App\Events\CommentCreated;
use App\Models\Task;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Console\Command;

class TestBroadcasting extends Command
{
    protected $signature = 'app:test-broadcasting';
    protected $description = 'Test broadcasting events directly';

    public function handle()
    {
        $this->info('🧪 Testing broadcasting events...');

        // Test TaskUpdated event
        $task = Task::first();
        if ($task) {
            $this->info("📢 Broadcasting TaskUpdated for task: {$task->title}");
            event(new TaskUpdated($task->load(['project', 'assignee'])));
            $this->info("✅ TaskUpdated event dispatched to channel: project.{$task->project_id}");
        } else {
            $this->warn("⚠️ No tasks found to test TaskUpdated event");
        }

        // Test CommentCreated event
        $comment = Comment::with(['task.project', 'author'])->first();
        if ($comment) {
            $this->info("📢 Broadcasting CommentCreated for comment: {$comment->body}");
            event(new CommentCreated($comment));
            $this->info("✅ CommentCreated event dispatched to channel: project.{$comment->task->project_id}");
        } else {
            $this->warn("⚠️ No comments found to test CommentCreated event");
        }

        $this->info('');
        $this->info('🎯 Events dispatched! If Reverb server is running and client is connected,');
        $this->info('   the events should appear in real-time on the /live page.');
        
        return 0;
    }
}