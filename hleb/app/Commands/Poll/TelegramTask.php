<?php
declare(strict_types=1);

namespace App\Commands\Poll;

use Hleb\Base\Task;

class TelegramTask extends Task
{
    protected function run(): int
    {
        echo '[HLEB TASK INFO] Minimal HLEB Task (Poll/TelegramTask) EXECUTED! v2' . PHP_EOL; // v2 для отслеживания
        return self::SUCCESS_CODE;
    }
}