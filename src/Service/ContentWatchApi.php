<?php

namespace App\Service;

use Exception;
use Psr\Log\LoggerInterface;

class ContentWatchApi
{
    private $curl;

    public function __construct(private readonly string $contentWatchApiKey, private readonly string $contentWatchApiTest)
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_TCP_FASTOPEN, true);
        curl_setopt($this->curl, CURLOPT_TCP_KEEPALIVE, true);
    }

    public function __destruct()
    {
        curl_close($this->curl);
    }

    public function checkContent($text): ?int
    {
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, [
            'key' => $this->contentWatchApiKey,
            'text' => $text,
            'test' => $this->contentWatchApiTest
        ]);
        curl_setopt($this->curl, CURLOPT_URL, 'https://content-watch.ru/public/api/');

        $response = curl_exec($this->curl);
        if ($response === false) {
            throw new Exception('Curl error: ' . curl_error($this->curl));
        }

        $data = json_decode(trim($response), true);
        if (!isset($data['error'])) {
            throw new Exception('API request error.');
        }
        if (!empty($data['error'])) {
            throw new Exception('API error: ' . $data['error']);
        }

        return $data['percent'] ?? null;
    }
}