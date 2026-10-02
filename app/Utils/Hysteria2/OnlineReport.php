<?php

namespace App\Utils\Hysteria2;

/**
 * Hysteria2 在线状态上报的纯判定逻辑.
 *
 * Hysteria2 没有节点端上报通道，面板只能轮询每个节点的流量统计 API：
 * - GET /online 返回「客户端ID -> 该客户端的连接数」的映射，键是鉴权时返回的 id，即面板用户ID；
 * - 一个节点可能配置多个 IP，每张网卡都是独立的 hysteria 实例，各有各的映射。
 *
 * 这里只放不依赖数据库与 HTTP 的判定，以便单测（本项目的 Laravel 测试需要可达的数据库才能启动）。
 */
final class OnlineReport
{
    /** 心跳续算窗口：hysteria2:sync 每分钟一轮，允许漏跑一轮而不视为断线 */
    public const UPTIME_CONTINUITY = 150;

    /** 首次观测到节点时的运行时长基数，与其它协议的上报量级保持一致 */
    public const UPTIME_SEED = 60;

    /**
     * 合并一次轮询里各地址的 /online 响应.
     *
     * @param  array<int, mixed>  $responses  每个地址的解码结果，null 表示该地址不可达或响应无法解析
     * @return array{reachable: bool, user_ids: array<int, int>}
     */
    public static function merge(array $responses): array
    {
        $userIds = [];
        $reachable = false;

        foreach ($responses as $response) {
            // 空映射同样是有效响应（节点正常，只是当前无人在线），只有拿不到对象才算联系不上
            if (! is_array($response)) {
                continue;
            }

            $reachable = true;

            foreach (array_keys($response) as $id) {
                if (is_numeric($id)) { // 非面板用户ID的键既不计入在线，也不会被用来踢下线
                    $userIds[(int) $id] = true;
                }
            }
        }

        // 多IP节点的在线用户取并集：同一个用户同时连在多张网卡上仍然只是一个在线用户，
        // 直接累加各IP的人数会把他重复计入。
        return ['reachable' => $reachable, 'user_ids' => array_keys($userIds)];
    }

    /**
     * 按准入名单切分节点上报的在线用户.
     *
     * 只有面板当前仍会放行的用户才算在线；其余（被禁用、过期、失去该节点权限、已被删除）
     * 必须踢下线，否则会一直留在节点的会话表里被计入在线人数。
     *
     * @param  array<int, int>  $userIds  节点上报的在线用户ID
     * @param  array<int, int>  $eligibleIds  该节点当前仍然放行的用户ID
     * @return array{online: array<int, int>, revoked: array<int, int>}
     */
    public static function split(array $userIds, array $eligibleIds): array
    {
        return [
            'online' => array_values(array_intersect($userIds, $eligibleIds)),
            'revoked' => array_values(array_diff($userIds, $eligibleIds)),
        ];
    }

    /**
     * 续算节点运行时长.
     *
     * Hysteria2 的接口不提供进程启动时间，只能用「连续观测」推算：只有上一轮心跳仍在续算窗口内
     * 才累加间隔，否则视为中间断过线（节点重启或面板采集漏跑），重新计数。时间回拨同样重新计数。
     */
    public static function uptime(?int $previousUptime, ?int $previousLogTime, int $now): int
    {
        if ($previousUptime === null || $previousLogTime === null || $now < $previousLogTime || $now - $previousLogTime > self::UPTIME_CONTINUITY) {
            return self::UPTIME_SEED;
        }

        return $previousUptime + ($now - $previousLogTime);
    }
}
