<?php

namespace App\Controller;

use Luzrain\TelegramBotApi\Method;
use Luzrain\TelegramBotApi\Type;
use Luzrain\TelegramBotBundle\Attribute\OnCommand;
use Luzrain\TelegramBotBundle\TelegramBot\TelegramCommand;

final class StartCommandController extends TelegramCommand
{
    // You can pass command arguments next to $message.
    // Be aware to set default values for arguments as they won't necessarily will be passed
    #[OnCommand('/start')]
    public function __invoke(Type\Message $message, string $arg1 = '', string $arg2 = ''): Method
    {
        return $this->reply('Hello from Mega bot');
    }
}
