<?php

namespace App\Message;

class ContentWatchQueue
{
    public function __construct(
        private string $content,
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }
}