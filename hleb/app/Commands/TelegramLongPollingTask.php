<?php

declare(strict_types=1);

namespace App\Commands;

use Hleb\Base\Task;
use Telegram\Bot\Api;
use App\Commands\Telegram\StartCommand; // To potentially use the command class
use Throwable;

class TelegramLongPollingTask extends Task
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected string $name = 'telegram:poll'; // How to call it from console

    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = 'Run Telegram bot long polling';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function execute(): int
    {
        $this->comment('Starting Telegram Bot Long Polling...');

        try {
            // Ensure TELEGRAM_BOT_TOKEN is loaded from .env or config
            // HLEB specific way to get env variables might be needed, e.g. \Hleb\Constructor\Data\SystemSettings::getParam('telegram', 'token')
            // For now, directly using getenv or $_ENV for simplicity, assuming it's loaded.
            $token = $_ENV['TELEGRAM_BOT_TOKEN'] ?? getenv('TELEGRAM_BOT_TOKEN');

            if (!$token) {
                $this->error('TELEGRAM_BOT_TOKEN not found in environment variables.');
                return 1; // Error
            }

            $telegram = new Api($token);

            // Register your command. This might be done differently depending on
            // how irazasyed/telegram-bot-sdk is best used without Laravel.
            // For now, we'll manually check for the command text.
            // $telegram->addCommand(StartCommand::class);

            $offset = 0;

            $this->comment('Listening for updates...');

            while (true) {
                try {
                    $updates = $telegram->getUpdates(['offset' => $offset, 'timeout' => 30]);

                    foreach ($updates as $update) {
                        $offset = $update->getUpdateId() + 1;

                        if ($update->getMessage()) {
                            $message = $update->getMessage();
                            $text = $message->getText();
                            $chatId = $message->getChat()->getId();

                            $this->info("Received message: " . $text . " from chat ID: " . $chatId);

                            if ($text === '/start') {
                                // Directly send a reply for now for simplicity
                                $telegram->sendMessage([
                                    'chat_id' => $chatId,
                                    'text' => 'Hello! Welcome from HLEB Long Polling Task.'
                                ]);
                                $this->info("Sent /start reply to chat ID: " . $chatId);
                            }
                            // Add more command handling here or delegate to command classes
                        }
                    }
                } catch (Throwable $e) {
                    $this->error('Error in polling loop: ' . $e->getMessage());
                    // Wait a bit before retrying to avoid spamming in case of persistent errors
                    sleep(10);
                }
                // Small delay to prevent high CPU usage if there are no updates or in case of rapid errors
                usleep(500000); // 0.5 seconds
            }
        } catch (Throwable $e) {
            $this->error('Failed to initialize Telegram Bot: ' . $e->getMessage());
            return 1; // Error
        }

        return 0; // Success (though this loop is infinite)
    }
}
