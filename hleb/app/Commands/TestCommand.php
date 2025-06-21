<?php
declare(strict_types=1);

namespace App\Commands;

use Hleb\Base\Task;

class TestCommand extends Task
{
    /**
     * Имя консольной команды.
     * Пример запуска: php hleb/console test:hello
     */
    protected ?string $name = 'test:hello';

    /**
     * Описание команды, будет отображаться в списке команд.
     */
    protected string $description = 'Простая тестовая команда для проверки регистрации команд.';

    /**
     * Выполнение команды.
     */
    public function execute(): int
    {
        $this->output('Привет от TestCommand!');
        $this->output('Эта команда успешно зарегистрирована и выполнена.');
        return self::SUCCESS_CODE;
    }
}
