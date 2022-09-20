<?php

namespace App\Observers;

use App\Components\DDNS;
use App\Jobs\VNet\reloadNode;
use App\Models\Node;
use Arr;
use Log;
use Str;

class NodeObserver
{
    public function saved(Node $node): void
    {
        $node->refresh_geo();
    }

    public function created(Node $node): void
    {
        if (! $node->auth()->create(['key' => Str::random(), 'secret' => Str::random(8)])) {
            Log::error('节点生成-自动生成授权时出现错误，请稍后自行生成授权！');
        }

        if ($node->is_ddns === '0' && $node->server && sysConfig('ddns_mode')) {
            if ($node->ip) {
                foreach ($node->ips() as $ip) {
                    DDNS::store($node->server, $ip);
                }
            }
            if ($node->ipv6) {
                foreach ($node->ips(6) as $ip) {
                    DDNS::store($node->server, $ip, 'AAAA');
                }
            }
        }
    }

    public function updated(Node $node): void
    {
        if ($node->is_ddns === '0' && sysConfig('ddns_mode')) {
            $changes = $node->getChanges();
            if (Arr::hasAny($changes, ['ip', 'ipv6', 'server'])) { // DDNS操作
                if (Arr::exists($changes, 'server')) { // 域名变动
                    if ($node->getOriginal('server')) {
                        DDNS::destroy($node->getOriginal('server')); // 删除原域名
                    }
                    if ($node->ip) { // 添加IPV4至新域名
                        foreach ($node->ips() as $ip) {
                            DDNS::store($node->server, $ip);
                        }
                    }
                    if ($node->ipv6) { // 添加IPV6至新域名
                        foreach ($node->ips(6) as $ip) {
                            DDNS::store($node->server, $ip, 'AAAA');
                        }
                    }
                } else { // 域名未改动
                    if (Arr::exists($changes, 'ip')) { // IPV4变动
                        DDNS::destroy($node->server, 'A');
                        if ($node->ip) { // 非空值 重新设置IPV4
                            foreach ($node->ips() as $ip) {
                                DDNS::store($node->server, $ip);
                            }
                        }
                    }
                    if (Arr::exists($changes, 'ipv6')) { // IPV6变动
                        DDNS::destroy($node->server, 'AAAA');
                        if ($node->ipv6) { // 非空值 重新设置IPV6
                            foreach ($node->ips(6) as $ip) {
                                DDNS::store($node->server, $ip, 'AAAA');
                            }
                        }
                    }
                }
            }
        }

        if ($node->type === '4') {
            reloadNode::dispatch($node);
        }
    }

    public function deleted(Node $node): void
    {
        if ($node->server && sysConfig('ddns_mode')) {
            DDNS::destroy($node->server);
        }
    }
}
