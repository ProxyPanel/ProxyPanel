<?php

namespace App\Jobs\VNet;

use App\Models\User;
use Arr;
use Http;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Throwable;

class addUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $data;
    private $nodes;

    public function __construct($userIds, $nodes)
    {
        $this->nodes = $nodes;
        $data = [];
        foreach (User::findMany($userIds) as $user) {
            $data[] = [
                'uid' => $user->id,
                'port' => $user->port,
                'passwd' => $user->passwd,
                'speed_limit' => $user->speed_limit,
                'enable' => $user->enable,
            ];
        }

        $this->data = $data;
    }

    public function handle(): void
    {
        if ($this->nodes instanceof Collection) {
            foreach ($this->nodes as $node) {
                $this->send(($node->server ?: $node->ip).':'.$node->push_port, $node->auth->secret);
            }
        } else {
            $this->send(($this->nodes->server ?: $this->nodes->ip).':'.$this->nodes->push_port, $this->nodes->auth->secret);
        }
    }

    private function send($host, $secret): void
    {
        $request = Http::baseUrl($host)->timeout(20)->withHeaders(['secret' => $secret]);

        $response = $request->post('api/v2/user/add/list', $this->data);
        $message = $response->json();
        if ($message && Arr::has($message, ['success', 'content']) && $response->ok()) {
            if ($message['success'] === 'false') {
                Log::warning('【新增用户】推送失败（推送地址：'.$host.'，返回内容：'.$message['content'].'）');
            } else {
                Log::info('【新增用户】推送成功（推送地址：'.$host.'，内容：'.json_encode($this->data, true).'）');
            }
        }
    }

    // 队列失败处理
    public function failed(Throwable $exception)
    {
        Log::error('【新增用户】推送异常：'.$exception->getMessage());
    }
}
