<?php

namespace App\Jobs\VNet;

use Arr;
use Http;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Throwable;

class delUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $userIds;
    private $nodes;

    public function __construct($userIds, $nodes)
    {
        $this->userIds = $userIds;
        $this->nodes = $nodes;
    }

    public function handle(): void
    {
        foreach ($this->nodes as $node) {
            $this->send(($node->server ?: $node->ip).':'.$node->push_port, $node->auth->secret);
        }
    }

    private function send($host, $secret): void
    {
        $request = Http::baseUrl($host)->timeout(15)->withHeaders(['secret' => $secret]);

        if (is_array($this->userIds)) {
            $response = $request->post('api/v2/user/del/list', $this->userIds);
        } else {
            $response = $request->post('api/user/del/'.$this->userIds);
        }

        $message = $response->json();
        if ($message && Arr::has($message, ['success', 'content']) && $response->ok()) {
            if ($message['success'] === 'false') {
                Log::alert('【删除用户】推送失败（推送地址：'.$host.'，返回内容：'.$message['content'].'）');
            } else {
                Log::notice('【删除用户】推送成功（推送地址：'.$host.'，内容：'.json_encode($this->userIds, true).'）');
            }
        }
    }

    // 队列失败处理
    public function failed(Throwable $exception)
    {
        Log::alert('【删除用户】推送异常：'.$exception->getMessage());
    }
}
