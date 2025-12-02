<?php

namespace App\Jobs;

use App\Models\Comment;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class SendTelegramCommentCreatedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $maxExceptions = 3;
    public int $timeout = 60;

    protected Comment $comment;
    protected string $chatId;

    /**
     * Create a new job instance.
     */
    public function __construct(Comment $comment, string $chatId)
    {
        $this->comment = $comment;
        $this->chatId = $chatId;
    }

    /**
     * Execute the job.
     */
    public function handle(TelegramService $telegramService): void
    {
        try {
            // Load relationships
            $this->comment->load(['user', 'task']);
            
            $result = $telegramService->sendCommentCreatedMessage($this->comment, $this->chatId);
            
            if ($result === null) {
                throw new Exception('Failed to send Telegram comment created message');
            }
            
            Log::info('Telegram comment created notification sent', [
                'comment_id' => $this->comment->id,
                'task_id' => $this->comment->task_id,
                'chat_id' => $this->chatId,
                'message_id' => $result['result']['message_id'] ?? null
            ]);
        } catch (Exception $e) {
            Log::error('Telegram comment created notification failed', [
                'comment_id' => $this->comment->id,
                'task_id' => $this->comment->task_id,
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
        Log::error('Telegram comment created notification failed permanently', [
            'comment_id' => $this->comment->id,
            'task_id' => $this->comment->task_id,
            'chat_id' => $this->chatId,
            'error' => $exception->getMessage()
        ]);
    }
}