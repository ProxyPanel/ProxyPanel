<?php

namespace App\Components;

use App\Http\Models\Config;
use App\Http\Models\EmailLog;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Log;

class ServerChan
{
    protected static $systemConfig;

    function __construct()
    {
        self::$systemConfig = Helpers::systemConfig();
    }

    /**
     * @param string $title   消息标题
     * @param string $content 消息内容
     *
     * @return string
     */
    public function send($title, $content)
    {
        $client = new Client();

        try {
            $response = $client->request('GET', 'https://sc.ftqq.com/' . self::$systemConfig['server_chan_key'] . '.send', [
                'query' => [
                    'text' => $title,
                    'desp' => $content
                ]
            ]);

            $result = json_decode($response->getBody());
            if (!$result->errno) {
                $this->addEmailLog(1, '[ServerChan]' . $title, $content);
            } else {
                $this->addEmailLog(1, '[ServerChan]' . $title, $content, 0, $result->errmsg);
            }
        } catch (RequestException $e) {
            Log::error(Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                Log::error(Psr7\str($e->getResponse()));
            }
        }
    }

    /**
     * 写入邮件发送日志
     *
     * @param int    $user_id 用户ID
     * @param string $title   标题
     * @param string $content 内容
     * @param int    $status  投递状态
     * @param string $error   投递失败时记录的异常信息
     */
    private function addEmailLog($user_id, $title, $content, $status = 1, $error = '')
    {
        $emailLogObj = new EmailLog();
        $emailLogObj->user_id = $user_id;
        $emailLogObj->title = $title;
        $emailLogObj->content = $content;
        $emailLogObj->status = $status;
        $emailLogObj->error = $error;
        $emailLogObj->created_at = date('Y-m-d H:i:s');
        $emailLogObj->save();
    }
}