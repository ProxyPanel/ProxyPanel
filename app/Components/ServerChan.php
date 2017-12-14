<?php

namespace App\Components;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Log;

class ServerChan
{
    /**
     * @param string $title 消息标题
     * @param string $content 消息内容
     * @param string $key ServerChan上申请的SCKEY
     * @return string
     */
    public function send($title, $content, $key)
    {
        $client = new Client();

        try {
            $response = $client->request('GET', 'https://sc.ftqq.com/' . $key . '.send', [
                'query' => [
                    'text' => $title,
                    'desp' => $content
                ]
            ]);

            return json_decode($response->getBody());
        } catch (RequestException $e) {
            Log::error(Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                Log::error(Psr7\str($e->getResponse()));
            }
        }
    }
}