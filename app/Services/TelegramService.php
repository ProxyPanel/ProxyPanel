<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    private string $api;

    public function __construct(?string $token = null)
    {
        $this->api = 'https://api.telegram.org/bot'.($token ?? sysConfig('telegram_token')).'/';
    }

    public function sendMessage(int $chatId, string $text, string $parseMode = ''): array
    {
        return $this->request('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
        ]);
    }

    private function request(string $method, array $params = [], bool $usePost = false): array
    {
        $http = Http::timeout(30);

        if ($usePost) {
            $response = $http->post($this->api.$method, $params);
        } else {
            $response = $http->get($this->api.$method.'?'.http_build_query($params));
        }

        $data = $response->json();
        if ($response->ok()) {
            return $data;
        }

        abort(500, '来自 TG 的错误：'.json_encode($data));
    }

    public function getMe(): array
    {
        return $this->request('getMe');
    }

    public function setWebhook(array $config): array
    {
        return $this->request('setWebhook', $config, true);
    }
}
