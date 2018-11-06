<?php

namespace App\Components;

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
     * 推送消息
     *
     * @param string $title   消息标题
     * @param string $content 消息内容
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
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
                Helpers::addServerChanLog($title, $content);
            } else {
                Helpers::addServerChanLog($title, $content, 0, $result->errmsg);
            }
        } catch (RequestException $e) {
            Log::error(Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                Log::error(Psr7\str($e->getResponse()));
            }
        }
    }
}