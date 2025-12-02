<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use App\Jobs\SendTelegramCommentCreatedJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendTelegramCommentNotification
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
    public function handle(CommentCreated $event): void
    {
        $chatId = config('services.telegram.chat_id');
        
        if (empty($chatId)) {
            Log::warning('Telegram chat ID not configured for comment notification', [
                'comment_id' => $event->comment->id
            ]);
            return;
        }

        Log::info('Dispatching Telegram comment notification', [
            'comment_id' => $event->comment->id,
            'task_id' => $event->comment->task_id,
            'chat_id' => $chatId
        ]);

        SendTelegramCommentCreatedJob::dispatch($event->comment, $chatId);
    }
}