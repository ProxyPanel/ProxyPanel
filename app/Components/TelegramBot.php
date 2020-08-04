<?php

namespace App\Components;

use App\Http\Models\EmailLog;
use Log;
use Telegram\Bot\Api;

/**
 * Telegram操作类
 *
 * Class Telegram
 *
 * @package App\Components
 */
class TelegramBot
{
    public static function send($title, $content)
    {
        if (Helpers::systemConfig()['is_telegram']) {
            $telegram = new Api(Helpers::systemConfig()['telegram_token']);
            try {
                $response = $telegram->sendMessage([
                    'chat_id'    => Helpers::systemConfig()['telegram_chatid'],
                    'text'       => $content,
                    'parse_mode' => 'Markdown'
                ]);

                if ($response->getMessageId()) {
                    self::addLog($title, $content);
                } else {
                    self::addLog($title, $content, 0, 'Telegram消息推送失败');
                }
            } catch (\Exception $e) {
                Log::error('Telegram消息推送异常：' . $e);
            }
        } else {
            Log::error('消息推送失败：未启用或未正确配置Telegram');
        }
    }

    /**
     * 添加Telegram推送日志
     *
     * @param string $title   标题
     * @param string $content 内容
     * @param int    $status  投递状态
     * @param string $error   投递失败时记录的异常信息
     *
     * @return int
     */
    private static function addLog($title, $content, $status = 1, $error = '')
    {
        $code = Helpers::makeEmailLogCode();

        $log = new EmailLog();
        $log->type = 4;
        $log->code = $code;
        $log->address = 'admin';
        $log->title = $title;
        $log->content = $content;
        $log->status = $status;
        $log->error = $error;

        return $log->save();
    }
}
