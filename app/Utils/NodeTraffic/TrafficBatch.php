<?php

namespace App\Utils\NodeTraffic;

use App\Models\User;

/**
 * 节点上报用户流量的批量处理.
 *
 * 节点每隔一段时间上报一次「用户ID => 本次上传/下载」，面板要把每一行落成流量记录、并累加到账号上。
 * 逐行做「校验 + INSERT + 查用户 + UPDATE」时查询数会到 4N（一个几千用户的节点每分钟就是上万次查询），
 * 所以这里只放不依赖数据库的聚合与 SQL 拼装，真正的执行交给控制器。
 * 这样也便于单测：本项目的 Laravel 测试需要可达的数据库才能启动，纯逻辑才可以真正跑起来。
 */
final class TrafficBatch
{
    /** 单条 UPDATE 最多合并多少个用户：再长就要开始担心 max_allowed_packet 了 */
    public const CHUNK_SIZE = 500;

    /**
     * 把上报记录聚合成「用户ID => 本次增量」，并乘上节点倍率.
     *
     * 同一个 uid 在同一批里出现多次会合并成一行；负值按 0 处理，否则会让已用流量倒退
     * （user.u / user.d 本身也是 unsigned，写入负值在严格模式下会直接报错）。
     *
     * @param  array<int, array{uid: int|string, upload: int|float|string, download: int|float|string}>  $records
     * @return array<int, array{u: int, d: int}>
     */
    public static function aggregate(array $records, float $rate): array
    {
        $traffic = [];

        foreach ($records as $record) {
            $uid = (int) $record['uid'];

            $traffic[$uid]['u'] = ($traffic[$uid]['u'] ?? 0) + max((int) ($record['upload'] * $rate), 0);
            $traffic[$uid]['d'] = ($traffic[$uid]['d'] ?? 0) + max((int) ($record['download'] * $rate), 0);
        }

        return $traffic;
    }

    /**
     * 生成流量记录（user_traffic_log）的批量插入行.
     *
     * 显式带上 node_id：用 insert() 批量写入时不会像 createMany() 那样自动补关联字段。
     *
     * @param  array<int, array{u: int, d: int}>  $traffic
     * @return array<int, array<string, int|float|string>>
     */
    public static function buildLogRows(array $traffic, int $nodeId, float $rate, int $logTime): array
    {
        $rows = [];

        foreach ($traffic as $uid => $row) {
            $rows[] = [
                'user_id' => $uid,
                'node_id' => $nodeId,
                'u' => $row['u'],
                'd' => $row['d'],
                'rate' => $rate,
                'traffic' => formatBytes($row['u'] + $row['d']),
                'log_time' => $logTime,
            ];
        }

        return $rows;
    }

    /**
     * 按 CHUNK_SIZE 把聚合结果切成多块，每块对应一条 UPDATE.
     *
     * 保留原键（array_chunk 的第三个参数必须为 true）：buildUpdateQuery() 依赖「键就是用户ID」。
     *
     * @param  array<int, array{u: int, d: int}>  $traffic
     * @return array<int, array<int, array{u: int, d: int}>>
     */
    public static function chunks(array $traffic): array
    {
        return array_chunk($traffic, self::CHUNK_SIZE, true);
    }

    /**
     * 生成「一条 UPDATE 累加整批用户流量」的语句与绑定值.
     *
     * 累加必须发生在数据库侧（u = u + x）：先读出来、在 PHP 里加完再写回去，
     * 会在两轮上报（或多节点）并发时互相覆盖，丢掉其中一份流量。
     *
     * 绑定值顺序与占位符出现顺序一致：u 的 CASE 对、d 的 CASE 对、t、WHERE 的 id 列表。
     *
     * @param  array<int, array{u: int, d: int}>  $traffic
     * @return array{sql: string, bindings: array<int, int>}
     */
    public static function buildUpdateQuery(array $traffic, int $logTime): array
    {
        $whenUpload = '';
        $whenDownload = '';
        $bindings = [];

        foreach ($traffic as $uid => $row) {
            $whenUpload .= ' WHEN ? THEN ?';
            $bindings[] = $uid;
            $bindings[] = $row['u'];
        }

        foreach ($traffic as $uid => $row) {
            $whenDownload .= ' WHEN ? THEN ?';
            $bindings[] = $uid;
            $bindings[] = $row['d'];
        }

        $table = (new User)->getTable();
        $ids = implode(',', array_fill(0, count($traffic), '?'));

        $sql = "UPDATE {$table} SET u = u + CASE id{$whenUpload} ELSE 0 END, d = d + CASE id{$whenDownload} ELSE 0 END, t = ? WHERE id IN ({$ids})";

        $bindings[] = $logTime;

        foreach (array_keys($traffic) as $uid) {
            $bindings[] = $uid;
        }

        return ['sql' => $sql, 'bindings' => $bindings];
    }
}
