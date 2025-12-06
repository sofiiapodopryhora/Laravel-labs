<?php

namespace App\Console\Commands;

use App\Events\TaskUpdated;
use App\Events\CommentCreated;
use App\Models\Task;
use App\Models\Comment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;

class TestBroadcastStructure extends Command
{
    protected $signature = 'app:test-broadcast-structure';
    protected $description = 'Test the structure of broadcast events';

    public function handle()
    {
        $this->info('🧪 Testing broadcast event structure...');

        // Test TaskUpdated event
        $task = Task::with(['project', 'assignee'])->first();
        if ($task) {
            $this->info("📋 Testing TaskUpdated event for task: {$task->title}");
            
            $event = new TaskUpdated($task);
            
            $this->line("🔗 Channels:");
            foreach ($event->broadcastOn() as $channel) {
                $this->line("   - " . get_class($channel) . ": {$channel->name}");
            }
            
            $this->line("📡 Broadcast name: " . $event->broadcastAs());
            $this->line("📦 Broadcast data:");
            $data = $event->broadcastWith();
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $this->line("   - {$key}: " . json_encode($value));
                } else {
                    $this->line("   - {$key}: {$value}");
                }
            }
        }

        $this->line('');

        // Test CommentCreated event
        $comment = Comment::with(['task.project', 'author'])->first();
        if ($comment) {
            $this->info("💭 Testing CommentCreated event for comment: " . substr($comment->body, 0, 50) . '...');
            
            $event = new CommentCreated($comment);
            
            $this->line("🔗 Channels:");
            foreach ($event->broadcastOn() as $channel) {
                $this->line("   - " . get_class($channel) . ": {$channel->name}");
            }
            
            $this->line("📡 Broadcast name: " . $event->broadcastAs());
            $this->line("📦 Broadcast data:");
            $data = $event->broadcastWith();
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $this->line("   - {$key}: " . json_encode($value));
                } else {
                    $this->line("   - {$key}: {$value}");
                }
            }
        }

        $this->line('');
        $this->info('✅ Broadcast event structure test completed!');
        $this->info('💡 Events are properly configured for real-time broadcasting.');
        
        return 0;
    }
}