<?php

namespace App\Jobs\Hysteria2;

use App\Models\Node;
use Exception;
use Http;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Throwable;

class DelUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private array|int $uids, private Collection|Node $nodes)
    {
        if (! $nodes instanceof Collection) {
            $this->nodes = new Collection([$nodes]);
        }

        if (! is_array($this->uids)) {
            $this->uids = [$this->uids];
        }
    }

    public function handle(): void
    {
        foreach ($this->nodes as $node) {
            if ($node->is_ddns) {
                $this->send($node->server.':'.$node->push_port, $node->auth->secret, $this->uids);
            } else { // 多IP支持
                foreach ($node->ips() as $ip) {
                    $this->send($ip.':'.$node->push_port, $node->auth->secret, $this->uids);
                }
            }
        }
    }

    private function send(string $host, string $secret, array $uids): void
    {
        try {
            $request = Http::baseUrl($host)->timeout(15)->withHeader('Authorization', $secret)->asJson();
            $response = $request->post('/kick', $uids);

            if (! $response->ok()) {
                Log::alert("【删除用户】推送失败（推送地址：{$host}）", $uids);
            } else {
                Log::info("【删除用户】推送成功（推送地址：{$host}）", ['uids' => $uids, 'response' => $response->json()]);
            }
        } catch (Exception $exception) {
            Log::alert('【删除用户】推送异常：'.$exception->getMessage(), ['uids' => $uids]);
        }
    }

    // 队列失败处理
    public function failed(Throwable $exception): void
    {
        Log::alert('【删除用户】推送异常：'.$exception->getMessage());
    }
}
