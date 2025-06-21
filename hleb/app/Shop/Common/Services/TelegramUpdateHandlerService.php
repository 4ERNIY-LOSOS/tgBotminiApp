<?php
declare(strict_types=1);

namespace App\Shop\Common\Services;

// Нам нужно убедиться, что TelegramService корректно настроен для использования SDK,
// и что тип объекта Update указан правильно для irazasyed/telegram-bot-sdk v3.x
// Обычно это Telegram\Bot\Objects\Update
use Telegram\Bot\Objects\Update;
use Hleb\Static\Log; // Фасад логирования Hleb

class TelegramUpdateHandlerService
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Обрабатывает одно обновление от Telegram.
     *
     * @param \Telegram\Bot\Objects\Update $update Объект обновления от Telegram.
     * @return void
     */
    public function processUpdate(Update $update): void
    {
        if ($update->getMessage()) {
            $message = $update->getMessage();
            $chatId = $message->getChat()->getId();
            $text = $message->getText();

            Log::info("Обработка сообщения от chat_id: " . $chatId . "; текст: " . $text);

            if ($text === '/start') {
                $this->handleStartCommand($chatId);
            }
            // TODO: Добавить обработчики для других команд/сообщений
        } elseif ($update->getCallbackQuery()) {
            // TODO: Обработать callback-запросы (нажатия на inline-кнопки)
        }
        // TODO: Добавить обработчики для других типов обновлений
    }

    /**
     * Обрабатывает команду /start.
     * Отправляет приветственное сообщение.
     *
     * @param int $chatId ID чата.
     */
    private function handleStartCommand(int $chatId): void
    {
        Log::info("Выполнение handleStartCommand для chat_id: " . $chatId);
        $this->telegramService->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Бот работает! Добро пожаловать!'
        ]);
    }
}
```
