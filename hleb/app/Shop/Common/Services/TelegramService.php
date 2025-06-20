<?php
namespace App\Shop\Common\Services;

use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Hleb\Static\Log; // Hleb's logging facade

class TelegramService
{
    private ?Api $telegram = null;
    private string $botToken;

    public function __construct(?string $botToken = null)
    {
        if ($botToken === null) {
            // Assuming hleb/config/telegram.php exists and returns ['bot_token' => 'YOUR_TOKEN']
            // And Hleb's config() helper can access it.
            $this->botToken = config('telegram.bot_token');
        } else {
            $this->botToken = $botToken;
        }

        if (!empty($this->botToken)) {
            try {
                $this->telegram = new Api($this->botToken);
            } catch (TelegramSDKException $e) {
                Log::error('Telegram SDK initialization failed: ' . $e->getMessage());
                $this->telegram = null; // Ensure it's null if init fails
            }
        } else {
            Log::warning('Telegram Bot Token is not configured.');
        }
    }

    public function isInitialized(): bool
    {
        return $this->telegram !== null;
    }

    /**
     * Returns the underlying Telegram Bot SDK Api instance.
     *
     * @return \Telegram\Bot\Api|null The Api instance if initialized, null otherwise.
     */
    public function getSdk(): ?Api
    {
        return $this->telegram;
    }

    public function getWebhookUpdate()
    {
        if (!$this->isInitialized()) {
            return null;
        }
        try {
            return $this->telegram->getWebhookUpdate();
        } catch (TelegramSDKException $e) {
            Log::error('Telegram getWebhookUpdate failed: ' . $e->getMessage());
            return null;
        }
    }

    public function sendMessage(array $params): ?\Telegram\Bot\Objects\Message
    {
        if (!$this->isInitialized()) {
            return null;
        }
        try {
            return $this->telegram->sendMessage($params);
        } catch (TelegramSDKException $e) {
            Log::error('Telegram sendMessage failed: ' . $e->getMessage(), $params);
            return null;
        }
    }

    public function setWebhook(string $url, array $params = []): bool
    {
        if (!$this->isInitialized()) {
             return false;
        }
        try {
            return $this->telegram->setWebhook(['url' => $url] + $params);
        } catch (TelegramSDKException $e) {
            Log::error('Telegram setWebhook failed: ' . $e->getMessage(), ['url' => $url]);
            return false;
        }
    }
}
