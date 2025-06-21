<?php
declare(strict_types=1);

namespace App\Commands;

use Hleb\Base\Task;

class TestCommand extends Task
{
    // protected ?string $name = 'test:hello'; // Убираем явное имя, Hleb сгенерирует 'test-command'

    /**
     * Простая тестовая команда для проверки регистрации команд.
     * Эта строка будет описанием в списке команд.
     *
     * Пример запуска: php hleb/console test-command
     *
     * @return int Код завершения команды.
     */
    protected function run(): int
    {
        $this->output('Привет от TestCommand!');
        $this->output('Эта команда успешно зарегистрирована и выполнена (если вы это видите).');
        return self::SUCCESS_CODE;
    }
}
