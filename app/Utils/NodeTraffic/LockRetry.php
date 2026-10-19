<?php

namespace App\Utils\NodeTraffic;

use Throwable;

/**
 * 节点上报写入的死锁有界重试：死锁由数据库当场回滚整笔事务，重跑同一批才是安全处置。
 *
 * 1205（lock wait timeout）不在重跑范围：它已等满 innodb_lock_wait_timeout，重跑只会再等一轮同样的时长；
 * 且重试期间 (node, report_id) 认领键一直被本次请求占着，节点超时后的整批重投会因认领键被当成已入账而当场丢弃。
 */
final class LockRetry
{
    /** 死锁最多重跑几次（含首次执行） */
    public const ATTEMPTS = 3;

    /**
     * 执行 $write()，只在死锁时重跑，返回实际执行次数.
     */
    public static function run(callable $write, int $attempts = self::ATTEMPTS): int
    {
        for ($execution = 1; ; $execution++) {
            try {
                $write();

                return $execution;
            } catch (Throwable $e) {
                if (! self::isDeadlock($e) || $execution >= $attempts) {
                    throw $e;
                }

                // 随机抖动：同一节点的多批上报不该在同一时刻一起醒来继续抢同一把锁
                usleep(random_int(20000, 150000));
            }
        }
    }

    /**
     * 死锁判据，取框架并发错误判据的死锁子集（不含 lock wait timeout 那条）；按消息串判，覆盖三种数据库的文案.
     */
    public static function isDeadlock(Throwable $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, 'Deadlock found when trying to get lock')
            || str_contains($message, 'deadlock detected')
            || str_contains($message, 'has been chosen as the deadlock victim');
    }
}
