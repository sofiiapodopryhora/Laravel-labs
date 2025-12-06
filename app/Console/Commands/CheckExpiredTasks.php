<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Jobs\SendTelegramMessageJob;
use App\Models\SchedulerLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckExpiredTasks extends Command
{
    protected $signature = 'app:check-expired-tasks';
    protected $description = 'Check for tasks in progress for more than 7 days and mark them as expired';

    public function handle()
    {
        $startTime = now();
        $this->info('Starting expired tasks check...');

        try {
            // Find tasks that are in_progress for more than 7 days
            $expiredTasks = Task::where('status', 'in_progress')
                ->where('created_at', '<', Carbon::now()->subDays(7))
                ->get();

            $expiredCount = $expiredTasks->count();

            if ($expiredCount === 0) {
                $this->info('No expired tasks found.');
                $this->logSchedulerExecution('check-expired-tasks', 'success', "No expired tasks found", $startTime);
                return;
            }

            foreach ($expiredTasks as $task) {
                // Update task status to expired
                $task->update(['status' => 'expired']);

                // Send Telegram notification
                $assigneeName = $task->assignee ? $task->assignee->name : 'Unassigned';
                $message = "⚠️ Task expired!\n\n" .
                          "Task: {$task->title}\n" .
                          "Project: {$task->project->name}\n" .
                          "Assignee: {$assigneeName}\n" .
                          "Created: {$task->created_at->format('Y-m-d H:i:s')}\n" .
                          "Status changed to: EXPIRED";

                $chatId = config('services.telegram.chat_id');
                if ($chatId) {
                    SendTelegramMessageJob::dispatch($chatId, $message);
                }

                $this->info("Task ID {$task->id} marked as expired and notification sent.");
            }

            $message = "Successfully processed {$expiredCount} expired tasks";
            $this->info($message);
            
            $this->logSchedulerExecution('check-expired-tasks', 'success', $message, $startTime);

        } catch (\Exception $e) {
            $errorMessage = "Error checking expired tasks: " . $e->getMessage();
            $this->error($errorMessage);
            $this->logSchedulerExecution('check-expired-tasks', 'error', $errorMessage, $startTime);
            
            return 1;
        }

        return 0;
    }

    private function logSchedulerExecution(string $command, string $status, string $message, Carbon $startTime)
    {
        try {
            $duration = max(0, now()->diffInSeconds($startTime));
            
            SchedulerLog::create([
                'command' => $command,
                'status' => $status,
                'message' => $message,
                'started_at' => $startTime,
                'finished_at' => now(),
                'duration' => $duration
            ]);
        } catch (\Exception $e) {
            $this->error("Failed to log scheduler execution: " . $e->getMessage());
        }
    }
}