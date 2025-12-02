<?php

namespace App\Console\Commands;

use App\Jobs\SendTelegramMessageJob;
use Illuminate\Console\Command;

class TestTelegramQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test-queue {message?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Telegram message sending via queue';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $chatId = config('services.telegram.chat_id');

        if (empty($chatId)) {
            $this->error('Telegram chat ID is not configured in .env file');
            return 1;
        }

        $message = $this->argument('message') ?: '🚀 Тестове повідомлення через чергу!';

        $this->info('Dispatching Telegram message to queue...');
        $this->line("Chat ID: {$chatId}");
        $this->line("Message: {$message}");

        try {
            SendTelegramMessageJob::dispatch($chatId, $message);
            $this->info('✅ Message job dispatched successfully!');
            $this->line('Run "php artisan queue:work" to process the job');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error dispatching job: ' . $e->getMessage());
            return 1;
        }
    }
}