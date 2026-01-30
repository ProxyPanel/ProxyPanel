<?php

namespace App\Jobs\Hysteria2;

use App\Models\Node;
use Exception;
use Http;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Throwable;

class GetStreamDump implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Node $node)
    {
    }

    public function handle(): void
    {
        if ($this->node->is_ddns) {
            $this->send($this->node->server.':'.$this->node->push_port, $this->node->auth->secret);
        } else { // 多IP支持
            foreach ($this->node->ips() as $ip) {
                $this->send($ip.':'.$this->node->push_port, $this->node->auth->secret);
            }
        }
    }

    private function send(string $host, string $secret): void
    {
        try {
            $request = Http::baseUrl($host)->timeout(15)->withHeader('Authorization', $secret);
            $response = $request->get('/dump/streams');

            $data = $response->json();
            if ($data && isset($data['streams'])) {
                // TODO: 这里可以处理流数据，例如记录到日志或存储到数据库
                // 目前只是获取数据，可以根据需要进一步处理
                Log::info('【流详情】获取成功', ['node_id' => $this->node->id, 'streams_count' => count($data['streams'])]);
            }
        } catch (Exception $exception) {
            Log::alert('【流详情】获取异常：'.$exception->getMessage());
        }
    }

    // 队列失败处理
    public function failed(Throwable $exception): void
    {
        Log::alert('【流详情】获取异常：'.$exception->getMessage());
    }
}
