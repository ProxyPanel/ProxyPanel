<?php

namespace App\Utils\NodeTraffic;

/**
 * 节点在线上报的纯判定逻辑.
 *
 * 只放不依赖数据库与 HTTP 的部分，便于单测（本项目的 Laravel 测试需要可达的数据库才能启动）。
 * 行按 uid 升序是锁序判据：node_online_ip 的 user_id/node_id 上都有外键，多行 INSERT 按行序给父行加锁，
 * 必须与 TrafficBatch 的流量 UPDATE 取同一把顺序，并发上报才不会互锁成环。
 */
final class OnlineBatch
{
    /**
     * 把上报记录整理成可入库的行，并挑出面板已没有的账号.
     *
     * 失效账号必须跳过而不是拒收整批：node_online_ip.user_id 上有外键，写进去直接报错，
     * 而节点每轮上报后就把本地在线表清空，拒收等于丢掉这一整分钟所有用户的在线记录。
     *
     * @param  array<int, array{uid: int|string, ip: string}>  $records
     * @param  array<int, int|string>  $ports  用户ID => 端口，来自一次 whereIn 查询
     * @return array{rows: array<int, array<string, mixed>>, missing: array<int, int>}
     */
    public static function build(array $records, array $ports, int $nodeId, int $logTime): array
    {
        $rows = [];
        $missing = [];

        foreach ($records as $record) {
            $uid = (int) $record['uid'];

            if (! array_key_exists($uid, $ports)) {
                $missing[] = $uid;

                continue;
            }

            $rows[] = [
                'node_id' => $nodeId,
                'user_id' => $uid,
                'ip' => $record['ip'],
                'port' => (int) $ports[$uid],
                'created_at' => $logTime,
            ];
        }

        // uid 升序是锁序判据：与 TrafficBatch::aggregate() 同一条，并发上报取 user 行锁的顺序一致才不会互锁成环
        usort($rows, fn (array $a, array $b): int => $a['user_id'] <=> $b['user_id']);

        return ['rows' => $rows, 'missing' => array_values(array_unique($missing))];
    }
}
