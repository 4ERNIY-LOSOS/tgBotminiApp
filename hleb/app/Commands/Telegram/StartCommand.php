<?php

declare(strict_types=1);

namespace App\Commands\Telegram;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram; // We'll use the SDK's API directly later, this is a common alias

/**
 * Class StartCommand.
 */
class StartCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected string $name = 'start';

    /**
     * @var string Command Description
     */
    protected string $description = 'Start Command to greet the user';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $this->replyWithMessage(['text' => 'Hello! Welcome to our bot.']);
    }
}
