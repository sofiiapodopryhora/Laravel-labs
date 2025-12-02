<?php

namespace App\Jobs;

use App\Models\Task;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class SendTelegramTaskCreatedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $maxExceptions = 3;
    public int $timeout = 60;

    protected Task $task;
    protected string $chatId;

    /**
     * Create a new job instance.
     */
    public function __construct(Task $task, string $chatId)
    {
        $this->task = $task;
        $this->chatId = $chatId;
    }

    /**
     * Execute the job.
     */
    public function handle(TelegramService $telegramService): void
    {
        try {
            // Load relationships
            $this->task->load(['project', 'assignedUser']);
            
            $result = $telegramService->sendTaskCreatedMessage($this->task, $this->chatId);
            
            if ($result === null) {
                throw new Exception('Failed to send Telegram task created message');
            }
            
            Log::info('Telegram task created notification sent', [
                'task_id' => $this->task->id,
                'chat_id' => $this->chatId,
                'message_id' => $result['result']['message_id'] ?? null
            ]);
        } catch (Exception $e) {
            Log::error('Telegram task created notification failed', [
                'task_id' => $this->task->id,
                'chat_id' => $this->chatId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Telegram task created notification failed permanently', [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'chat_id' => $this->chatId,
            'error' => $exception->getMessage()
        ]);
    }
}