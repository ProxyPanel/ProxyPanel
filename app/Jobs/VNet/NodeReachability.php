<?php

namespace App\Jobs\VNet;

/**
 * 面板→节点方向的寻址地址可达性窗口.
 *
 * 登记判据只有一条：传输层没成功（连不上、超时）。被登记的地址一定发不出请求，所以跳过它不改变
 * 投递结果（仍算未送达），只是不再为必然烧满连接超时的地址占住 worker 与队列重投。
 * 注销只有两条路：窗口到期，或运维显式重载节点。
 *
 * 投递 Job 的 $backoff 取 [30, 本窗口]：第一次重投保持快（非 2xx、反代错误页那类失败不登记窗口，
 * 30 秒后真发得出去），末位不小于本窗口——落在窗口里的那次一个字节都发不出去，却照样烧掉一次 tries，
 * 等于把「一次超时」直接变成一行 failed_jobs。
 */
final class NodeReachability
{
    /** 退避窗口：对账每 10 分钟一轮，窗口内最多探一次，节点恢复后最多滞后一个窗口重新被探测 */
    public const TTL = Minute * 10;

    public static function unreachable(string $host): bool
    {
        return cache()->has(self::key($host));
    }

    public static function mark(string $host): void
    {
        cache()->put(self::key($host), 1, self::TTL);
    }

    public static function clear(string $host): void
    {
        cache()->forget(self::key($host));
    }

    private static function key(string $host): string
    {
        return 'vnetUnreachable_'.md5($host);
    }
}
