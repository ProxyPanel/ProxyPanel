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
            Log::warning('节点生成-自动生成授权时出现错误，请稍后自行生成授权！');
        }

        if ($node->is_ddns === '0' && $node->server && sysConfig('ddns_mode')) {
            if ($node->ip) {
                DDNS::store($node->server, $node->ip);
            }
            if ($node->ipv6) {
                DDNS::store($node->server, $node->ipv6, 'AAAA');
            }
        }
    }

    public function updated(Node $node): void
    {
        if ($node->is_ddns === '0' && sysConfig('ddns_mode')) {
            $changes = $node->getChanges();
            if (Arr::hasAny($changes, ['ip', 'ipv6', 'server'])) { // DDNS操作
                if (Arr::exists($changes, 'server')) { // 域名变动
                    DDNS::destroy($node->getOriginal('server')); // 删除原域名
                    if ($node->ip) { // 添加IPV4至新域名
                        DDNS::store($node->server, $node->ip);
                    }
                    if ($node->ipv6) { // 添加IPV6至新域名
                        DDNS::store($node->server, $node->ipv6, 'AAAA');
                    }
                } else {
                    if (Arr::exists($changes, 'ip')) { // 域名未改动，IP变动
                        if ($node->ip && $node->getOriginal('ip')) { // IPV4变动
                            DDNS::update($node->server, $node->ip);
                        } elseif ($node->ip) { // 新添加IPV4
                            DDNS::store($node->server, $node->ip);
                        } else { // 空值 删除原IPV4
                            DDNS::destroy($node->server, 'A');
                        }
                    }
                    if (Arr::exists($changes, 'ipv6')) { // 域名未改动，IPV6变动
                        if ($node->ipv6 && $node->getOriginal('ipv6')) { // IPV6变动
                            DDNS::update($node->server, $node->ipv6, 'AAAA');
                        } elseif ($node->ipv6) { // 新添加IPV6
                            DDNS::store($node->server, $node->ipv6, 'AAAA');
                        } else { // 空值 删除原IPV6
                            DDNS::destroy($node->server, 'AAAA');
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
        if ($node->is_ddns === 0 && $node->server && sysConfig('ddns_mode')) {
            DDNS::destroy($node->server);
        }
    }
}
