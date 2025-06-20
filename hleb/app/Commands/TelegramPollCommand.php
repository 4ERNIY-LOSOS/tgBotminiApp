<?php
declare(strict_types=1);

namespace App\Commands;

use Hleb\Base\Task;
use App\Shop\Common\Services\TelegramService;
use App\Shop\Common\Services\TelegramUpdateHandlerService;
use Hleb\Static\Log;
use Throwable; // For catching all kinds of errors/exceptions

// Assuming the Update object will be from the Telegram Bot SDK
// Adjust the namespace if it's different for irazasyed/telegram-bot-sdk v3.x
// Usually it's Telegram\Bot\Objects\Update
use Telegram\Bot\Objects\Update;


class TelegramPollCommand extends Task
{
    /**
     * Command to start long polling for Telegram bot updates.
     * To run: php hleb/console telegram:poll
     *
     * @description Starts polling Telegram for bot updates.
     */
    protected string $description = "Starts polling Telegram for bot updates.";

    /**
     * The name of the console command.
     * If not specified, it will be generated automatically from the class name.
     * // Example: php console telegram:poll
     */
    protected ?string $name = 'telegram:poll'; // Custom command name

    private ?TelegramService $telegramService = null;
    private ?TelegramUpdateHandlerService $updateHandler = null;
    private int $offset = 0;
    private const POLLING_TIMEOUT = 30; // Seconds for long polling timeout
    private const LOOP_SLEEP = 1; // Seconds to sleep if no updates or on error, before retrying

    /**
     * Initializes services.
     * It's better if Hleb's DI container can inject these,
     * but direct instantiation is also possible if services are simple or singletons.
     * For console commands, constructor injection might not be standard.
     * We can try to get them from the container or instantiate directly.
     */
    private function initServices(): bool
    {
        if ($this->telegramService && $this->updateHandler) {
            return true;
        }
        try {
            // Attempt to get from container if available, or instantiate directly
            // This is a simplified approach. A proper service locator or DI might be better.
            $this->telegramService = new TelegramService(); // Assumes TelegramService has a simple constructor or is a singleton managed by itself

            if (!$this->telegramService->isInitialized()) {
                Log::error("TelegramPollCommand: TelegramService could not be initialized. Check token and SDK setup.");
                $this->output("Error: TelegramService could not be initialized. Check logs.");
                return false;
            }

            $this->updateHandler = new TelegramUpdateHandlerService($this->telegramService);
            return true;
        } catch (Throwable $e) {
            Log::error("TelegramPollCommand: Failed to initialize services: " . $e->getMessage());
            $this->output("Error: Failed to initialize services. " . $e->getMessage());
            return false;
        }
    }

    /**
     * Execute the console command.
     */
    public function execute(): int
    {
        $this->output("Starting Telegram long polling...");
        if (!$this->initServices()) {
            return self::ERROR_CODE;
        }

        // Optional: Clear any pending webhooks first (important if switching from webhook)
        // This should ideally be a one-time operation when deploying the polling bot.
        // Can be done manually via curl or a separate command.
        // For now, we assume webhook is already cleared or not conflicting.
        // Example: try { $this->telegramService->getSdk()->deleteWebhook(); $this->output("Webhook cleared."); } catch (Throwable $e) { $this->output("Could not clear webhook: " . $e->getMessage()); }


        while (true) { // Infinite loop for continuous polling
            try {
                if (!$this->telegramService->isInitialized()) {
                    // This check is a bit redundant if initServices worked, but good for safety in a long loop
                    Log::error("TelegramPollCommand: TelegramService not initialized in loop. Attempting to re-initialize.");
                    if (!$this->initServices()) { // Try to re-initialize
                        $this->output("Attempted to re-initialize services but failed. Sleeping before retry...");
                        sleep(self::LOOP_SLEEP * 5); // Longer sleep if re-init fails
                        continue;
                    }
                }

                // Fetch updates using long polling
                // The SDK's getUpdates method should handle long polling.
                // The 'timeout' parameter tells Telegram to keep the connection open.
                $updates = $this->telegramService->getSdk()->getUpdates([
                    'offset' => $this->offset,
                    'timeout' => self::POLLING_TIMEOUT,
                ]);

                if (!empty($updates)) {
                    foreach ($updates as $update) {
                        if ($update instanceof Update) {
                            $this->output("Processing update ID: " . $update->getUpdateId());
                            $this->updateHandler->processUpdate($update);
                            $this->offset = $update->getUpdateId() + 1; // Important: update offset
                        } else {
                            Log::warning("Received an update that is not an instance of Telegram Bot SDK Update object.");
                        }
                    }
                } else {
                    // No updates received during this poll, just loop again.
                    // $this->output("No updates in this poll."); // Can be too verbose
                }
            } catch (Throwable $e) {
                Log::error("TelegramPollCommand: Error during polling loop: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
                $this->output("Error during polling: " . $e->getMessage() . ". Sleeping before retry...");
                // Wait a bit before retrying to avoid spamming in case of persistent errors
                sleep(self::LOOP_SLEEP * 5);
            }
            // Optional: Add a small sleep here if you want to reduce CPU usage when there are no updates,
            // though long polling timeout should handle most of the waiting.
            // sleep(self::LOOP_SLEEP);
        }

        // This part of the code will not be reached due to the infinite loop.
        // $this->output("Telegram long polling stopped.");
        // return self::SUCCESS_CODE;
    }
}

```
