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
    // protected string $description = "Starts polling Telegram for bot updates."; // Удалено, описание будет из PHPDoc метода run()

    /**
     * Запускает long polling для получения обновлений от Telegram.
     * Эта строка будет описанием в списке команд.
     *
     * Имя команды будет 'telegram:poll'.
     * Пример запуска: php hleb/console telegram:poll
     */
    protected ?string $name = 'telegram:poll'; // Возвращаем явное имя команды

    private ?TelegramService $telegramService = null;
    private ?TelegramUpdateHandlerService $updateHandler = null;
    private int $offset = 0;
    private const POLLING_TIMEOUT = 30; // Секунды для таймаута long polling
    private const LOOP_SLEEP = 1;       // Секунды для ожидания при отсутствии обновлений или при ошибке перед повторной попыткой
    private const ERROR_LOOP_SLEEP = 5; // Секунды для ожидания при ошибке

    /**
     * Инициализирует сервисы.
     * Лучше, если DI-контейнер Hleb сможет их внедрить,
     * но прямое создание экземпляров также возможно, если сервисы простые или являются синглтонами.
     * Для консольных команд внедрение через конструктор может быть не стандартным.
     * Можно попытаться получить их из контейнера или создать напрямую.
     */
    private function initServices(): bool
    {
        Log::info("TelegramPollCommand: Попытка инициализации сервисов...");
        if ($this->telegramService && $this->updateHandler) {
            Log::info("TelegramPollCommand: Сервисы уже инициализированы.");
            return true;
        }
        try {
            Log::info("TelegramPollCommand: Инициализация TelegramService...");
            $this->telegramService = new TelegramService();

            if (!$this->telegramService->isInitialized()) {
                Log::error("TelegramPollCommand: TelegramService не удалось инициализировать. Проверьте токен и настройки SDK.");
                $this->output("Ошибка: TelegramService не удалось инициализировать. См. логи.");
                return false;
            }
            Log::info("TelegramPollCommand: TelegramService успешно инициализирован.");

            Log::info("TelegramPollCommand: Инициализация TelegramUpdateHandlerService...");
            $this->updateHandler = new TelegramUpdateHandlerService($this->telegramService);
            Log::info("TelegramPollCommand: TelegramUpdateHandlerService успешно инициализирован.");
            Log::info("TelegramPollCommand: Все сервисы успешно инициализированы.");
            return true;
        } catch (Throwable $e) {
            Log::error(sprintf(
                "TelegramPollCommand: Не удалось инициализировать сервисы: %s (Тип: %s) в файле %s на строке %d",
                $e->getMessage(),
                get_class($e),
                $e->getFile(),
                $e->getLine()
            ));
            $this->output("Ошибка: Не удалось инициализировать сервисы. " . $e->getMessage());
            return false;
        }
    }

    /**
     * Запускает long polling для получения обновлений Telegram.
     * Эта строка будет использована как описание команды в списке.
     * Имя команды будет 'telegram-poll-command' (автоматически из имени класса).
     * Пример запуска: php hleb/console telegram-poll-command
     *
     * @return int Код завершения команды.
     */
    protected function run(): int
    {
        Log::info("TelegramPollCommand: Запуск команды run()...");
        $this->output("Запуск Telegram long polling...");

        if (!$this->initServices()) {
            Log::error("TelegramPollCommand: Инициализация сервисов не удалась. Команда прервана.");
            return self::ERROR_CODE;
        }
        Log::info("TelegramPollCommand: Сервисы успешно инициализированы для run().");

        // Опционально: сначала очистить все ожидающие веб-хуки (важно при переключении с веб-хука)
        // В идеале это должна быть одноразовая операция при развертывании бота для опроса.
        // Можно сделать вручную через curl или отдельной командой.
        // Пока предполагаем, что веб-хук уже очищен или не конфликтует.
        // Пример: try { $this->telegramService->getSdk()->deleteWebhook(); $this->output("Веб-хук очищен."); } catch (Throwable $e) { $this->output("Не удалось очистить веб-хук: " . $e->getMessage()); }

        Log::info("TelegramPollCommand: Вход в основной цикл опроса...");
        while (true) { // Бесконечный цикл для непрерывного опроса
            try {
                if (!$this->telegramService || !$this->telegramService->isInitialized()) {
                    Log->error("TelegramPollCommand: TelegramService не инициализирован в цикле. Попытка повторной инициализации...");
                    if (!$this->initServices()) {
                        $this->output("Попытка повторной инициализации сервисов не удалась. Ожидание перед повтором...");
                        Log::error("TelegramPollCommand: Повторная инициализация не удалась. Ожидание " . self::ERROR_LOOP_SLEEP . " сек.");
                        sleep(self::ERROR_LOOP_SLEEP);
                        continue;
                    }
                    Log::info("TelegramPollCommand: Повторная инициализация сервисов в цикле прошла успешно.");
                }

                Log::debug("TelegramPollCommand: Запрос обновлений с offset: " . $this->offset . ", timeout: " . self::POLLING_TIMEOUT);
                $updates = $this->telegramService->getSdk()->getUpdates([
                    'offset' => $this->offset,
                    'timeout' => self::POLLING_TIMEOUT,
                ]);
                Log::debug("TelegramPollCommand: Получено " . count($updates) . " обновлений.");

                if (!empty($updates)) {
                    Log::info("TelegramPollCommand: Обработка " . count($updates) . " обновлений...");
                    foreach ($updates as $update) {
                        if ($update instanceof Update) {
                            $updateId = $update->getUpdateId();
                            Log::info("TelegramPollCommand: Обработка обновления ID: " . $updateId);
                            $this->output("Обработка обновления ID: " . $updateId);
                            $this->updateHandler->processUpdate($update);
                            $this->offset = $updateId + 1;
                            Log::debug("TelegramPollCommand: Новый offset: " . $this->offset);
                        } else {
                            Log::warning("TelegramPollCommand: Получено обновление, не являющееся объектом Telegram Bot SDK Update.");
                        }
                    }
                } else {
                     Log::debug("TelegramPollCommand: Обновлений нет в этом цикле опроса.");
                    // $this->output("Обновлений нет в этом цикле опроса."); // Может быть слишком многословно
                }
            } catch (Throwable $e) {
                Log::error(sprintf(
                    "TelegramPollCommand: Ошибка в цикле опроса: %s (Тип: %s) в файле %s на строке %d\nТрассировка: %s",
                    $e->getMessage(),
                    get_class($e),
                    $e->getFile(),
                    $e->getLine(),
                    $e->getTraceAsString()
                ));
                $this->output("Ошибка во время опроса: " . $e->getMessage() . ". Ожидание перед повтором...");
                sleep(self::ERROR_LOOP_SLEEP);
            }
            // Опционально: небольшая задержка для снижения нагрузки на ЦП, если нет обновлений,
            // хотя таймаут long polling должен обрабатывать большую часть ожидания.
            // sleep(self::LOOP_SLEEP);
        }

        // Эта часть кода не будет достигнута из-за бесконечного цикла.
        // Log::info("TelegramPollCommand: Опрос Telegram остановлен.");
        // $this->output("Опрос Telegram остановлен.");
        // return self::SUCCESS_CODE;
    }
}

```
