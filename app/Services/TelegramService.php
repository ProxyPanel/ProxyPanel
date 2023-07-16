<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    private static string $api;

    public function __construct(?string $token = null)
    {
        self::$api = 'https://api.telegram.org/bot'.($token ?? sysConfig('telegram_token')).'/';
    }

    public function sendMessage(int $chatId, string $text, string $parseMode = ''): array
    {
        return $this->request('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
        ]);
    }

    private function request(string $method, array $params = []): array
    {
        $response = Http::get(self::$api.$method.'?'.http_build_query($params));
        $data = $response->json();
        if ($response->ok()) {
            return $data;
        }

        abort(500, "来自TG的错误：$data");
    }

    public function getMe(): array
    {
        return $this->request('getMe');
    }

    public function setWebhook(string $url): array
    {
        return $this->request('setWebhook', ['url' => $url]);
    }
}
