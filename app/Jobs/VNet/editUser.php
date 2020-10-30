<?php

namespace App\Jobs\VNet;

use App\Models\User;
use Arr;
use Http;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Throwable;

class editUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $data;
    private $nodes;

    public function __construct(User $user, $nodes)
    {
        $this->nodes = $nodes;
        $this->data = [
            'uid'         => $user->id,
            'port'        => (int) $user->port,
            'passwd'      => $user->passwd,
            'speed_limit' => $user->speed_limit,
            'enable'      => (int) $user->enable,
        ];
    }

    public function handle(): void
    {
        foreach ($this->nodes as $node) {
            $host = ($node->server ?: $node->ip).':'.$node->push_port;
            $secret = $node->auth->secret;

            // 如果用户已存在节点内，则执行修改；否者为添加
            $list = $this->list($host, $secret);
            if ($list && in_array($this->data['uid'], $list)) {
                $this->send($host, $secret);
            } else {
                addUser::dispatchNow($this->data['uid'], $node);
            }
        }
    }

    private function list($host, $secret)
    {
        $request = Http::baseUrl($host)->timeout(15)->withHeaders(['secret' => $secret]);

        $response = $request->get('api/user/list');
        $message = $response->json();
        if ($message && $response->ok()) {
            return Arr::pluck($message, 'uid');
        }

        Log::warning('【用户列表】获取失败（推送地址：'.$host.'）');
        return false;
    }

    private function send($host, $secret): void
    {
        $request = Http::baseUrl($host)->timeout(15)->withHeaders(['secret' => $secret]);

        $response = $request->post('api/user/edit', $this->data);
        $message = $response->json();
        if ($message && Arr::has($message, ['success', 'content']) && $response->ok()) {
            if ($message['success'] === 'false') {
                Log::warning('【编辑用户】推送失败（推送地址：'.$host.'，返回内容：'.$message['content'].'）');
            } else {
                Log::info('【编辑用户】推送成功（推送地址：'.$host.'，内容：'.json_encode($this->data, true).'）');
            }
        }
    }

    // 队列失败处理
    public function failed(Throwable $exception)
    {
        Log::warning('【编辑用户】推送异常：'.$exception->getMessage());
    }
}
