<?php
declare(strict_types=1);

namespace App\Shop\Common\Services;

// We will need to ensure TelegramService is correctly set up to use the SDK
// and that this Update object type hint is correct for irazasyed/telegram-bot-sdk v3.x
// It's typically Telegram\Bot\Objects\Update
use Telegram\Bot\Objects\Update;
use Hleb\Static\Log;

class TelegramUpdateHandlerService
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Processes a single Telegram update.
     *
     * @param \Telegram\Bot\Objects\Update $update The update object from Telegram.
     * @return void
     */
    public function processUpdate(Update $update): void
    {
        if ($update->getMessage()) {
            $message = $update->getMessage();
            $chatId = $message->getChat()->getId();
            $text = $message->getText();

            Log::info("Processing message from chat_id: " . $chatId . "; text: " . $text);

            if ($text === '/start') {
                $this->handleStartCommand($chatId);
            }
            // TODO: Add more command/message handlers here
        } elseif ($update->getCallbackQuery()) {
            // TODO: Handle callback queries
        }
        // TODO: Add handlers for other update types
    }

    private function handleStartCommand(int $chatId): void
    {
        $miniAppUrl = config('telegram.mini_app_base_url');

        if (empty($miniAppUrl)) {
            // Simplified log message to avoid potential unicode escape issues in subtask
            Log::warning('Mini App URL is not configured in .env. Cannot send WebApp button.');
            $this->telegramService->sendMessage([
                'chat_id' => $chatId,
                // Simplified text to avoid issues with complex strings in this subtask
                'text' => 'Бот работает! Добро пожаловать! Mini App URL не настроен.'
            ]);
        } else {
            $finalMiniAppUrl = rtrim($miniAppUrl, '/') . '/shop_mini_app/';

            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        ['text' => '🛍️ Открыть Магазин (Mini App)', 'web_app' => ['url' => $finalMiniAppUrl]]
                    ]
                ]
            ];

            $this->telegramService->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Бот работает! Добро пожаловать!',
                'reply_markup' => json_encode($replyMarkup)
            ]);
        }
    }
}
```
