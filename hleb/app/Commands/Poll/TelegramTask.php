<?php
declare(strict_types=1);
namespace App\Commands\Poll; // Изменен неймспейс

use Hleb\Base\Task;

class TelegramTask extends Task // Изменено имя класса
{
    // Убраны свойства $name и $description

    // Метод переименован с execute на run и изменен на protected
    protected function run(): int
    {
        // Используем echo, так как $this->comment() может быть специфичен для другого базового класса команд
        // или требует дополнительной настройки вывода для Supervisor.
        // PHP_EOL для новой строки, чтобы лог был читаемым.
        echo '[HLEB TASK INFO] Minimal HLEB Task (Poll/TelegramTask) EXECUTED!' . PHP_EOL;

        // Для теста просто выйдем. В реальном poller'е здесь будет бесконечный цикл.
        // Если Supervisor настроен на перезапуск, он перезапустит эту задачу.
        // Мы должны увидеть это сообщение в логах Docker для контейнера php.
        return self::SUCCESS_CODE; // Используем константу HLEB
    }
}
