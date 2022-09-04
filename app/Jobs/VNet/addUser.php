<?php

namespace App\Jobs\VNet;

use App\Models\Node;
use App\Models\User;
use Arr;
use Exception;
use Http;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
        foreach (User::findMany($userIds) as $user) {
            $data[] = [
                'uid'         => $user->id,
                'port'        => $user->port,
                'passwd'      => $user->passwd,
                'speed_limit' => $user->speed_limit,
                'enable'      => $user->enable,
            ];
        }

        $this->data = $data ?? [];
    }

    public function handle(): void
    {
        foreach ($this->nodes as $node) {
            if ($node->is_ddns) {
                $this->send($node->server.':'.$node->push_port, $node->auth->secret);
            } else { // 多IP支持
                foreach ($node->ips() as $ip) {
                    $this->send($ip.':'.$node->push_port, $node->auth->secret);
                }
            }
        }
    }

    private function send(string $host, string $secret): void
    {
        try {
            $response = Http::baseUrl($host)->timeout(20)->withHeaders(['secret' => $secret])->post('api/v2/user/add/list', $this->data);
            $message = $response->json();
            if ($message && Arr::has($message, ['success', 'content']) && $response->ok()) {
                if ($message['success'] === 'false') {
                    Log::alert("【新增用户】推送失败（推送地址：{$host}，返回内容：".$message['content'].'）');
                } else {
                    Log::notice("【新增用户】推送成功（推送地址：{$host}，内容：".json_encode($this->data, true).'）');
                }
            }
        } catch (Exception $exception) {
            Log::alert('【新增用户】推送异常：'.$exception->getMessage());
        }
    }

    // 队列失败处理
    public function failed(Throwable $exception)
    {
        Log::alert('【新增用户】推送异常：'.$exception->getMessage());
    }
}
