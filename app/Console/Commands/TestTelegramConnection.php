<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TestTelegramConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Telegram Bot API connection';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegramService): int
    {
        $this->info('Testing Telegram Bot API connection...');

        try {
            // Test connection
            if (!$telegramService->testConnection()) {
                $this->error('Failed to connect to Telegram API');
                return 1;
            }

            $this->info('✅ Connection successful!');

            // Get bot info
            $botInfo = $telegramService->getBotInfo();
            if ($botInfo) {
                $this->info('Bot Information:');
                $this->line("Name: {$botInfo['result']['first_name']}");
                $this->line("Username: @{$botInfo['result']['username']}");
                $this->line("ID: {$botInfo['result']['id']}");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Error testing Telegram connection: ' . $e->getMessage());
            return 1;
        }
    }
}