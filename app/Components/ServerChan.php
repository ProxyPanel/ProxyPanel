<?php

namespace App\Components;

use App\Http\Models\EmailLog;
use Log;

class ServerChan
{
    /**
     * 推送消息
     *
     * @param string $title   消息标题
     * @param string $content 消息内容
     *
     * @return mixed
     */
    public static function send($title, $content)
    {
        if (Helpers::systemConfig()['is_server_chan'] && Helpers::systemConfig()['server_chan_key']) {
            try {
                $url = 'https://sc.ftqq.com/' . Helpers::systemConfig()['server_chan_key'] . '.send?text=' . $title . '&desp=' . urlencode($content);
                $response = Curl::send($url);
                $result = json_decode($response);
                if (!$result->errno) {
                    self::addLog($title, $content);
                } else {
                    self::addLog($title, $content, 0, $result->errmsg);
                }
            } catch (\Exception $e) {
                Log::error($e);
            }
        }
    }

    /**
     * 添加serverChan投递日志
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
        $log = new EmailLog();
        $log->type = 2;
        $log->address = 'admin';
        $log->title = $title;
        $log->content = $content;
        $log->status = $status;
        $log->error = $error;

        return $log->save();
    }
}