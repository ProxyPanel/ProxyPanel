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

        if ($node->is_ddns === 0 && $node->server && sysConfig('ddns_mode')) {
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
        if ($node->is_ddns === 0 && sysConfig('ddns_mode')) {
            $changes = $node->getChanges();
            if (Arr::hasAny($changes, ['ip', 'ipv6', 'server'])) {
                if (Arr::exists($changes, 'server')) {
                    DDNS::destroy($node->getOriginal('server'));
                    if ($node->ip) {
                        DDNS::store($node->server, $node->ip);
                    }
                    if ($node->ipv6) {
                        DDNS::store($node->server, $node->ipv6, 'AAAA');
                    }
                } else {
                    if (Arr::exists($changes, 'ip')) {
                        if ($node->ip && $node->getOriginal('ip')) {
                            DDNS::update($node->server, $node->ip);
                        } elseif ($node->ip) {
                            DDNS::store($node->server, $node->ip);
                        } else {
                            DDNS::destroy($node->server, 'A');
                        }
                    }
                    if (Arr::exists($changes, 'ipv6')) {
                        if ($node->ipv6 && $node->getOriginal('ipv6')) {
                            DDNS::update($node->server, $node->ipv6, 'AAAA');
                        } elseif ($node->ipv6) {
                            DDNS::store($node->server, $node->ipv6, 'AAAA');
                        } else {
                            DDNS::destroy($node->server, 'AAAA');
                        }
                    }
                }
            }
        }

        if ($node->type === 4) {
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
