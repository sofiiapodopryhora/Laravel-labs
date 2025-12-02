<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class TelegramService
{
    protected string $botToken;
    protected string $apiUrl;
    
    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->apiUrl = config('services.telegram.api_url', 'https://api.telegram.org');
        
        if (empty($this->botToken)) {
            throw new Exception('Telegram bot token is not configured.');
        }
    }
    
    /**
     * Send message to Telegram chat
     *
     * @param string $chatId
     * @param string $message
     * @param array $options Additional options (parse_mode, reply_markup, etc.)
     * @return array|null
     */
    public function sendMessage(string $chatId, string $message, array $options = []): ?array
    {
        $url = $this->apiUrl . "/bot{$this->botToken}/sendMessage";
        
        $payload = array_merge([
            'chat_id' => $chatId,
            'text' => $message,
        ], $options);
        
        try {
            Log::info('Sending Telegram message', [
                'chat_id' => $chatId,
                'message_length' => strlen($message),
                'options' => $options
            ]);
            
            $response = Http::timeout(30)->post($url, $payload);
            
            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('Telegram message sent successfully', [
                    'chat_id' => $chatId,
                    'message_id' => $responseData['result']['message_id'] ?? null
                ]);
                return $responseData;
            } else {
                Log::error('Telegram API error', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'chat_id' => $chatId
                ]);
                return null;
            }
        } catch (Exception $e) {
            Log::error('Failed to send Telegram message', [
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
                'message' => $message
            ]);
            return null;
        }
    }
    
    /**
     * Send formatted message about task creation
     *
     * @param object $task
     * @param string $chatId
     * @return array|null
     */
    public function sendTaskCreatedMessage($task, string $chatId): ?array
    {
        $message = "🆕 *Нова задача створена*\n\n";
        $message .= "📝 *Назва:* {$task->title}\n";
        $message .= "📄 *Опис:* " . (strlen($task->description) > 100 ? substr($task->description, 0, 100) . '...' : $task->description) . "\n";
        $message .= "📊 *Статус:* {$task->status}\n";
        $message .= "🗂 *Проект:* {$task->project->name}\n";
        $message .= "👤 *Виконавець:* " . ($task->assignedUser->name ?? 'Не призначено') . "\n";
        $message .= "📅 *Створено:* " . $task->created_at->format('d.m.Y H:i');
        
        return $this->sendMessage($chatId, $message, ['parse_mode' => 'Markdown']);
    }
    
    /**
     * Send formatted message about comment creation
     *
     * @param object $comment
     * @param string $chatId
     * @return array|null
     */
    public function sendCommentCreatedMessage($comment, string $chatId): ?array
    {
        $message = "💬 *Новий коментар*\n\n";
        $message .= "👤 *Автор:* {$comment->user->name}\n";
        $message .= "📝 *До задачі:* {$comment->task->title}\n";
        $message .= "💭 *Коментар:* " . (strlen($comment->body) > 150 ? substr($comment->body, 0, 150) . '...' : $comment->body) . "\n";
        $message .= "📅 *Створено:* " . $comment->created_at->format('d.m.Y H:i');
        
        return $this->sendMessage($chatId, $message, ['parse_mode' => 'Markdown']);
    }
    
    /**
     * Test connection to Telegram API
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        $url = $this->apiUrl . "/bot{$this->botToken}/getMe";
        
        try {
            $response = Http::timeout(10)->get($url);
            return $response->successful();
        } catch (Exception $e) {
            Log::error('Telegram connection test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Get bot information
     *
     * @return array|null
     */
    public function getBotInfo(): ?array
    {
        $url = $this->apiUrl . "/bot{$this->botToken}/getMe";
        
        try {
            $response = Http::timeout(10)->get($url);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return null;
        } catch (Exception $e) {
            Log::error('Failed to get Telegram bot info', ['error' => $e->getMessage()]);
            return null;
        }
    }
}