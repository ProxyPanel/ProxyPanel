<?php

namespace App\Channels;

use Exception;
use Helpers;
use Http;
use Illuminate\Notifications\Notification;
use Log;

class TgChatChannel
{
    /**
     * tg_chat_token 存的是整数 chat_id（群/超级群为负值，例如 -1001234567890）。
     * 库里可能仍存着第三方中继的 32 位十六进制 token，Telegram 对它会回 400 chat not found，所以按此格式校验。
     */
    public static function isValidChatId(?string $chatId): bool
    {
        return $chatId !== null && preg_match('/^-?\d+$/', trim($chatId)) === 1;
    }

    /**
     * 走 Telegram 官方 Bot API：机器人令牌取 telegram_token，收件人取 tg_chat_token.
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toCustom($notifiable);

        $token = sysConfig('telegram_token');
        $chatId = sysConfig('tg_chat_token');

        if (empty($token) || empty($chatId) || ! self::isValidChatId($chatId)) { // 配置不全/格式不对就别发请求，留一条日志便于排查
            Log::warning(trans('notification.error', [
                'channel' => trans('admin.system.notification.channel.tg_chat'),
                'reason' => '缺少 Telegram Token 或 Chat ID',
            ]));

            return false;
        }

        $text = $message['title'].PHP_EOL.'=========='.PHP_EOL.$message['content'];

        try {
            // 用 POST 而不是 GET：令牌与 chat_id 不该出现在 URL / 访问日志里
            $response = Http::timeout(15)->asForm()->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
            ]);
        } catch (Exception $e) {
            Log::critical(trans('notification.error', [
                'channel' => trans('admin.system.notification.channel.tg_chat'),
                'reason' => $e->getMessage(),
            ]));

            return false;
        }

        $ret = $response->json() ?? [];

        // Telegram 成功时返回 {"ok":true,...}；失败时 ok=false 并带 description
        if ($response->ok() && ($ret['ok'] ?? false)) {
            Helpers::addNotificationLog($message['title'], $message['content'], 6);

            return $ret;
        }

        Helpers::addNotificationLog($message['title'], $message['content'], 6, -1, $ret['description'] ?? $response->body());

        return false;
    }
}
