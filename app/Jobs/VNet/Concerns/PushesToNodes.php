<?php

namespace App\Jobs\VNet\Concerns;

use App\Jobs\VNet\NodeReachability;
use Arr;
use Exception;
use Http;
use Illuminate\Database\Eloquent\Collection;
use Log;

/**
 * 面板主动推 VNET 节点的一次投递：逐寻址地址 POST，带节点通信密钥。
 *
 * 「未送达」只指传输层没成功（连不上、非 2xx、响应体不是 JSON）——那才值得让队列重投；
 * 节点明确回绝（success=false）再重投也不会变，所以只记日志并算已送达。
 * 传输层失败会把寻址地址登记进退避窗口，窗口内不再发 HTTP，但地址仍按未送达交回调用方。
 */
trait PushesToNodes
{
    /**
     * @param  array|string|null  $payload  请求体，null 表示不带体
     * @return array 没送达的地址，交给 Job 决定是否重投（现在是抛异常）
     */
    protected function pushToNodes(Collection $nodes, string $endpoint, string $label, array|string|null $payload = null, int $timeout = 20): array
    {
        $undelivered = [];

        foreach ($nodes as $node) {
            foreach ($node->pushAddresses() as $address) {
                $host = $address.':'.$node->push_port;

                if (NodeReachability::unreachable($host)) {
                    $undelivered[] = $address;

                    continue;
                }

                if (! $this->pushOnce($host, $node->auth->secret, $endpoint, $label, $payload, $timeout)) {
                    $undelivered[] = $address;
                }
            }
        }

        return $undelivered;
    }

    private function pushOnce(string $host, string $secret, string $endpoint, string $label, array|string|null $payload, int $timeout): bool
    {
        try {
            $request = Http::baseUrl($host)->timeout($timeout)->withHeader('secret', $secret);
            $response = $payload === null ? $request->post($endpoint) : $request->post($endpoint, $payload);
            $message = $response->json();

            if (! $response->ok() || ! is_array($message)) { // 反代错误页或空体：这次没送到
                Log::warning("【{$label}】推送失败（推送地址：{$host}，状态：".$response->status().'）');

                return false;
            }

            if (Arr::has($message, ['success', 'content']) && $message['success'] === 'false') {
                $rejection = "【{$label}】节点回绝（推送地址：{$host}，返回内容：".$message['content'].'）';
                // 删除的回绝只有「节点上本来没这个人」一种含义（掉级换组也要显式 DelUser），属幂等 no-op
                str_contains($endpoint, 'user/del') ? Log::warning($rejection) : Log::alert($rejection);
            }

            return true;
        } catch (Exception $exception) {
            NodeReachability::mark($host);
            Log::alert('【'.$label.'】推送异常：'.$exception->getMessage());

            return false;
        }
    }
}
