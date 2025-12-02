<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Jobs\SendTelegramTaskCreatedJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendTelegramTaskNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TaskCreated $event): void
    {
        $chatId = config('services.telegram.chat_id');
        
        if (empty($chatId)) {
            Log::warning('Telegram chat ID not configured for task notification', [
                'task_id' => $event->task->id
            ]);
            return;
        }

        Log::info('Dispatching Telegram task notification', [
            'task_id' => $event->task->id,
            'task_title' => $event->task->title,
            'chat_id' => $chatId
        ]);

        SendTelegramTaskCreatedJob::dispatch($event->task, $chatId);
    }
}