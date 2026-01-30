<?php

namespace App\Observers;

use App\Events\NodeActions;
use App\Jobs\VNet\ReloadNode;
use App\Models\Node;
use App\Utils\DDNS;
use Arr;
use Exception;
use Str;

class NodeObserver
{
    // 辅助方法：发送广播消息
    private function broadcastMessage(string $type, array $data, ?int $nodeId = null): void
    {
        broadcast(new NodeActions($type, $data, $nodeId));
    }

    // 辅助方法：处理DDNS操作并发送广播
    private function handleDdnsOperation(string $type, DDNS $dns, string $operation, string $ip = '', string $recordType = '', ?int $nodeId = null): void
    {
        try {
            if ($operation === 'store') {
                $dns->store($ip, $recordType);
            } elseif ($operation === 'destroy') {
                $dns->destroy($recordType, $ip);
            }

            $this->broadcastMessage($type, ['operation' => 'handle_ddns', 'sub_operation' => $operation, 'data' => $ip, 'status' => 1], $nodeId);
        } catch (Exception $e) {
            $this->broadcastMessage($type, ['operation' => 'handle_ddns', 'sub_operation' => $operation, 'data' => $ip, 'status' => 0, 'message' => $e->getMessage()], $nodeId);
        }
    }

    // 处理IP变化的辅助方法
    private function updateIpChanges(DDNS $dns, string $originalIps, array $currentIps, int $nodeId, string $recordType = 'A'): void
    {
        $originalIpsArray = array_filter(array_map('trim', explode(',', $originalIps)));
        // 计算需要删除的IP (在原列表但不在新列表中)
        $ipsToDelete = array_diff($originalIpsArray, $currentIps);
        // 计算需要添加的IP (在新列表但不在原列表中)
        $ipsToAdd = array_diff($currentIps, $originalIpsArray);

        $this->broadcastMessage('update', ['operation' => 'handle_ddns', 'sub_operation' => 'list', 'delete' => array_values($ipsToDelete), 'add' => array_values($ipsToAdd), 'status' => 1], $nodeId);

        foreach ($ipsToDelete as $ip) {
            $this->handleDdnsOperation('update', $dns, 'destroy', $ip, $recordType, $nodeId);
        }

        foreach ($ipsToAdd as $ip) {
            $this->handleDdnsOperation('update', $dns, 'store', $ip, $recordType, $nodeId);
        }
    }

    public function created(Node $node): void
    {
        // GEO
        $geo = $node->refresh_geo();
        if (isset($geo['update'])) {
            $this->broadcastMessage('create', ['operation' => 'refresh_geo', 'status' => 1]);
        } else {
            $this->broadcastMessage('create', ['operation' => 'refresh_geo', 'status' => 0]);
        }

        if ($node->auth()->create(['key' => Str::random(), 'secret' => Str::random(8)])) {
            $this->broadcastMessage('create', ['operation' => 'create_auth', 'status' => 1]);
        } else {
            $this->broadcastMessage('create', ['operation' => 'create_auth', 'status' => 0, 'message' => trans('admin.node.operation.auth_failed')]);
        }

        if (! $node->is_ddns && $node->server && sysConfig('ddns_mode')) {
            $currentDNS = new DDNS($node->server);
            $ips4 = $node->ips();
            $ips6 = $node->ips(6);

            // 发送DDNS操作开始信号及IP列表
            $this->broadcastMessage('create', ['operation' => 'handle_ddns', 'sub_operation' => 'list', 'add' => array_merge($ips4, $ips6), 'status' => 1]);

            // 处理IPv4地址
            foreach ($ips4 as $ip) {
                $this->handleDdnsOperation('create', $currentDNS, 'store', $ip, 'A');
            }

            // 处理IPv6地址
            foreach ($ips6 as $ip) {
                $this->handleDdnsOperation('create', $currentDNS, 'store', $ip, 'AAAA');
            }
        }
    }

    public function updated(Node $node): void
    {
        // 在任何可能修改模型的操作之前保存原始值
        $originalServer = $node->getOriginal('server');
        $originalIp = $node->getOriginal('ip') ?? '';
        $originalIpv6 = $node->getOriginal('ipv6') ?? '';

        // GEO
        $geo = $node->refresh_geo();
        if (isset($geo['update'])) {
            $this->broadcastMessage('update', ['operation' => 'refresh_geo', 'status' => 1], $node->id);
        } else {
            $this->broadcastMessage('update', ['operation' => 'refresh_geo', 'status' => 0], $node->id);
        }

        // DDNS
        if (! $node->is_ddns && sysConfig('ddns_mode')) {
            $changes = $node->getChanges();

            if (Arr::hasAny($changes, ['ip', 'ipv6', 'server'])) {
                $currentDNS = new DDNS($node->server);

                if (Arr::has($changes, 'server')) {
                    $this->broadcastMessage('update', ['operation' => 'handle_ddns', 'sub_operation' => 'list', 'delete' => [$originalServer], 'add' => array_merge($node->ips(), $node->ips(6)), 'status' => 1], $node->id);
                    if ($originalServer) {
                        try {
                            (new DDNS($originalServer))->destroy();
                            $this->broadcastMessage('update', ['operation' => 'handle_ddns', 'sub_operation' => 'destroy', 'data' => $originalServer, 'status' => 1], $node->id);
                        } catch (Exception $e) {
                            $this->broadcastMessage('update', ['operation' => 'handle_ddns', 'sub_operation' => 'destroy', 'data' => $originalServer, 'status' => 0, 'message' => $e->getMessage()], $node->id);
                        }
                    }
                    foreach ($node->ips() as $ip) {
                        $this->handleDdnsOperation('update', $currentDNS, 'store', $ip, 'A', $node->id);
                    }
                    foreach ($node->ips(6) as $ip) {
                        $this->handleDdnsOperation('update', $currentDNS, 'store', $ip, 'AAAA', $node->id);
                    }
                } else {
                    if (Arr::has($changes, 'ip')) {
                        $this->updateIpChanges($currentDNS, $originalIp, $node->ips(), $node->id);
                    }

                    if (Arr::has($changes, 'ipv6')) {
                        $this->updateIpChanges($currentDNS, $originalIpv6, $node->ips(6), $node->id, 'AAAA');
                    }
                }
            } else {
                $this->broadcastMessage('update', ['operation' => 'handle_ddns', 'status' => 1, 'sub_operation' => 'unchanged'], $node->id);
            }
        }

        // Reload
        if ((int) $node->type === 4) {
            ReloadNode::dispatch($node);
            $this->broadcastMessage('update', ['operation' => 'reload_node', 'status' => 1], $node->id);
        }
    }

    public function deleted(Node $node): void
    {
        // 发送删除DDNS操作开始信号
        if ($node->server && sysConfig('ddns_mode')) {
            $this->broadcastMessage('delete', ['operation' => 'handle_ddns', 'sub_operation' => 'list', 'delete' => [$node->server], 'status' => 1], $node->id);
            try {
                (new DDNS($node->server))->destroy();
                $this->broadcastMessage('delete', ['operation' => 'handle_ddns', 'sub_operation' => 'destroy', 'data' => $node->server, 'status' => 1], $node->id);
            } catch (Exception $e) {
                $this->broadcastMessage('delete', ['operation' => 'handle_ddns', 'sub_operation' => 'destroy', 'data' => $node->server, 'status' => 0, 'message' => $e->getMessage()], $node->id);
            }
        }
    }
}
