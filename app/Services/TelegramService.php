<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    private $api;

    public function __construct(string $token = null)
    {
        $this->api = 'https://api.telegram.org/bot'.($token ?? sysConfig('telegram_token')).'/';
    }

    public function sendMessage(int $chatId, string $text, string $parseMode = '')
    {
        $this->request('sendMessage', [
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => $parseMode,
        ]);
    }

    public function getMe()
    {
        return $this->request('getMe');
    }

    public function setWebhook(string $url)
    {
        return $this->request('setWebhook', ['url' => $url]);
    }

    private function request(string $method, array $params = [])
    {
        $response = Http::get($this->api.$method.'?'.http_build_query($params));
        if (! $response->ok()) {
            abort(500, '来自TG的错误：'.$response->json());
        }

        return $response->json();
    }
}
