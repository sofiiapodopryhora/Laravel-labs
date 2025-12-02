<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramService;
use App\Jobs\SendTelegramMessageJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TelegramController extends Controller
{
    protected TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Test Telegram connection
     */
    public function testConnection(): JsonResponse
    {
        try {
            $isConnected = $this->telegramService->testConnection();
            
            if ($isConnected) {
                $botInfo = $this->telegramService->getBotInfo();
                return response()->json([
                    'success' => true,
                    'message' => 'Telegram connection successful',
                    'bot_info' => $botInfo['result'] ?? null
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to Telegram API'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing connection: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send test message directly
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:4096',
            'chat_id' => 'sometimes|string'
        ]);

        $chatId = $request->input('chat_id', config('services.telegram.chat_id'));
        $message = $request->input('message');

        if (empty($chatId)) {
            return response()->json([
                'success' => false,
                'message' => 'Chat ID not provided and not configured'
            ], 400);
        }

        try {
            $result = $this->telegramService->sendMessage($chatId, $message);
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'telegram_response' => $result
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send message'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send message via queue
     */
    public function sendMessageQueued(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:4096',
            'chat_id' => 'sometimes|string'
        ]);

        $chatId = $request->input('chat_id', config('services.telegram.chat_id'));
        $message = $request->input('message');

        if (empty($chatId)) {
            return response()->json([
                'success' => false,
                'message' => 'Chat ID not provided and not configured'
            ], 400);
        }

        try {
            SendTelegramMessageJob::dispatch($chatId, $message);
            
            return response()->json([
                'success' => true,
                'message' => 'Message queued successfully',
                'chat_id' => $chatId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error queuing message: ' . $e->getMessage()
            ], 500);
        }
    }
}