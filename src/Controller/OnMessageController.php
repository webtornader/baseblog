<?php

namespace App\Controller;

use Luzrain\TelegramBotApi\Event;
use Luzrain\TelegramBotApi\Method;
use Luzrain\TelegramBotApi\Type;
use Luzrain\TelegramBotBundle\Attribute\OnEvent;
use OpenAI\Client;

// It's not necessary to extend TelegramCommand
final class OnMessageController
{
    // Listen any available event from Event namespace
    private Client $openai;

    public function __construct(Client $openai)
    {
        $this->openai = $openai;
    }

    #[OnEvent(Event\Message::class)]
    public function __invoke(Type\Message $message): Method
    {
        $inlineKeyboard = new Type\InlineKeyboardMarkup(
            inlineKeyboard: Type\InlineKeyboardButtonArrayBuilder::create()
                                ->addButton(new Type\InlineKeyboardButton(text: 'Url button', url: 'https://google.com'))
                                ->addButton(new Type\InlineKeyboardButton(text: 'Callback button', callbackData: 'callback_data'))
                                ->addBreak()
                                ->addButton(new Type\InlineKeyboardButton(text: 'Iinline query', switchInlineQueryCurrentChat: 'test')),
        );

        $result = $this->openai->chat()->create([
                                                    'model' => 'gpt-4o-mini',
                                                    'messages' => [
                                                        ['role' => 'user', 'content' => $message->text],
                                                    ],
                                                ]);

//        echo $result['choices'][0]['text']; // an open-source, widely-used, server-side scripting language.
        $responseText = $result['choices'][0]['text'] ?? $result['choices'][0]['message']['content'] ?? 'No response text available';

        return new Method\SendMessage(
//            chatId: $message->chat->id,
            chatId: -1002674226763,
            text: $responseText, //print_r($result['choices'][0]['message']['content']),//
//            replyMarkup: $inlineKeyboard,
        );
    }
}