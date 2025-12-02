<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class SendTestTelegramMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:send-test {message?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test message to Telegram';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegramService): int
    {
        $chatId = config('services.telegram.chat_id');

        if (empty($chatId)) {
            $this->error('Telegram chat ID is not configured in .env file');
            $this->line('Add TELEGRAM_CHAT_ID to your .env file');
            return 1;
        }

        $message = $this->argument('message') ?: '🔥 Тестове повідомлення з Laravel додатка!';

        $this->info('Sending test message to Telegram...');
        $this->line("Chat ID: {$chatId}");
        $this->line("Message: {$message}");

        try {
            $result = $telegramService->sendMessage($chatId, $message);

            if ($result) {
                $this->info('✅ Message sent successfully!');
                $this->line("Message ID: {$result['result']['message_id']}");
                return 0;
            } else {
                $this->error('❌ Failed to send message');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Error sending Telegram message: ' . $e->getMessage());
            return 1;
        }
    }
}