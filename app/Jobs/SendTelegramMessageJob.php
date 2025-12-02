<?php

namespace App\Jobs;

use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class SendTelegramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $maxExceptions = 3;
    public int $timeout = 60;

    protected string $chatId;
    protected string $message;
    protected array $options;

    /**
     * Create a new job instance.
     */
    public function __construct(string $chatId, string $message, array $options = [])
    {
        $this->chatId = $chatId;
        $this->message = $message;
        $this->options = $options;
    }

    /**
     * Execute the job.
     */
    public function handle(TelegramService $telegramService): void
    {
        try {
            $result = $telegramService->sendMessage($this->chatId, $this->message, $this->options);
            
            if ($result === null) {
                throw new Exception('Failed to send Telegram message - API returned null');
            }
            
            Log::info('Telegram message job completed successfully', [
                'chat_id' => $this->chatId,
                'message_id' => $result['result']['message_id'] ?? null
            ]);
        } catch (Exception $e) {
            Log::error('Telegram message job failed', [
                'chat_id' => $this->chatId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);
            
            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Telegram message job failed permanently', [
            'chat_id' => $this->chatId,
            'message' => $this->message,
            'error' => $exception->getMessage()
        ]);
    }
}