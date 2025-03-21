<?php

namespace App\Controller;

use Luzrain\TelegramBotApi\Method;
use Luzrain\TelegramBotApi\Type;
use Luzrain\TelegramBotBundle\Attribute\OnCommand;
use Luzrain\TelegramBotBundle\TelegramBot\TelegramCommand;
use OpenAI\Client;

final class ListCommandController extends TelegramCommand
{
    private Client $openai;

    public function __construct(Client $openai)
    {
        $this->openai = $openai;
    }
    #[OnCommand('/list')]
    public function __invoke(Type\Message $message, string $arg1 = '', string $arg2 = ''): Method
    {
        $response = $this->openai->models()->list();
//print_r($response);
//        $response->object; // 'list'
//
        $list = [];
        foreach ($response->data as $result) {
            $list[] = $result->id; // 'gpt-3.5-turbo-instruct'
//            $result->object; // 'model'
            // ...
        }


        return $this->reply(print_r($list,true)); // ['object' => 'list', 'data' => [...]]
    }
}
