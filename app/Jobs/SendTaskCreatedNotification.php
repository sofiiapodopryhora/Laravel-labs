<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendTaskCreatedNotification implements ShouldQueue
{
    use Queueable;

    public Task $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function handle(): void
    {
        $this->task->load(['project', 'author', 'assignee']);

        $message = sprintf(
            'New task created: "%s" in project "%s" by %s%s',
            $this->task->title,
            $this->task->project->name,
            $this->task->author->name,
            $this->task->assignee ? " (assigned to {$this->task->assignee->name})" : ''
        );

        Log::info('Task Created Notification', [
            'task_id' => $this->task->id,
            'project_id' => $this->task->project_id,
            'author_id' => $this->task->author_id,
            'assignee_id' => $this->task->assignee_id,
            'title' => $this->task->title,
            'status' => $this->task->status,
            'priority' => $this->task->priority,
            'due_date' => $this->task->due_date,
            'message' => $message
        ]);
    }
}
