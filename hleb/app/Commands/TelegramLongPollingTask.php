<?php
declare(strict_types=1);
namespace App\Commands;
use Hleb\Base\Task;
class TelegramLongPollingTask extends Task
{
    protected string $name = 'telegram:poll';
    protected string $description = 'Test HLEB Task execution';
    public function execute(): int
    {
        $this->comment('Minimal TelegramLongPollingTask EXECUTED SUCCESSFULLY!');
        // Для теста просто выйдем, чтобы Supervisor не спамил перезапусками
        return 0; 
    }
}