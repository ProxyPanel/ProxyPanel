<?php

namespace App\Console\Commands;

use App\Models\Node;
use App\Models\NodeHeartbeat;
use App\Models\User;
use App\Notifications\NodeBlocked;
use App\Notifications\NodeOffline;
use App\Utils\NetworkDetection;
use Cache;
use Illuminate\Console\Command;
use Log;
use Notification;

class NodeStatusDetection extends Command
{
    protected $signature = 'nodeStatusDetection';

    protected $description = '节点状态检测';

    public function handle(): void
    {
        $jobTime = microtime(true);

        if (sysConfig('node_offline_notification')) {// 检测节点心跳是否异常
            $this->checkNodeStatus();
        }

        if (sysConfig('node_blocked_notification')) {// 监测节点网络状态
            $lastCheckTime = Cache::get('LastCheckTime');

            if (! $lastCheckTime || $lastCheckTime <= time()) {
                $this->checkNodeNetwork();
            } else {
                Log::info('下次节点阻断检测时间：'.date('Y-m-d H:i:s', $lastCheckTime));
            }
        }

        $jobTime = round(microtime(true) - $jobTime, 4);
        Log::info(__('----「:job」Completed, Used :time seconds ----', ['job' => $this->description, 'time' => $jobTime]));
    }

    private function checkNodeStatus(): void
    {
        $offlineCheckTimes = sysConfig('offline_check_times');
        $onlineNode = NodeHeartbeat::recently()->distinct()->pluck('node_id');

        $data = [];
        foreach (Node::whereRelayNodeId(null)->whereStatus(1)->whereNotIn('id', $onlineNode)->get() as $node) {
            // 近期无节点负载信息则认为是后端炸了
            if ($offlineCheckTimes > 0) {
                $cacheKey = 'offline_check_times'.$node->id;
                if (! Cache::has($cacheKey)) { // 已通知次数
                    Cache::put($cacheKey, 1, now()->addDay()); // 键将保留24小时
                } else {
                    $times = Cache::increment($cacheKey);
                    if ($times > $offlineCheckTimes) {
                        continue;
                    }
                }
            }
            $data[] = [
                'name' => $node->name,
                'host' => $node->host,
            ];
        }

        if (! empty($data)) {
            Notification::send(User::find(1), new NodeOffline($data));
        }
    }

    private function checkNodeNetwork(): void
    {
        $detectionCheckTimes = sysConfig('detection_check_times');

        foreach (Node::whereStatus(1)->where('detection_type', '<>', 0)->get() as $node) {
            $node_id = $node->id;
            $data = [];
            // 使用DDNS的node先通过gethostbyname获取ipv4地址
            foreach ($node->ips() as $ip) {
                if ($node->detection_type) {
                    $status = (new NetworkDetection)->networkStatus($ip, $node->port ?? 22);

                    if ($node->detection_type !== 1 && $status['icmp'] !== 1) {
                        $data[$node_id][$ip]['icmp'] = config('common.network_status')[$status['icmp']];
                    }

                    if ($node->detection_type !== 2 && $status['tcp'] !== 1) {
                        $data[$node_id][$ip]['tcp'] = config('common.network_status')[$status['tcp']];
                    }

                    sleep(2);
                }
            }

            // 节点检测次数
            if (! empty($data[$node_id]) && $detectionCheckTimes) {
                // 已通知次数
                $cacheKey = 'detection_check_times'.$node_id;

                if (! Cache::has($cacheKey)) { // 已通知次数
                    Cache::put($cacheKey, 1, now()->addHours(12)); // 键将保留12小时
                    $times = 1;
                } else {
                    $times = Cache::increment($cacheKey);
                }
                if ($times > $detectionCheckTimes) {
                    Cache::forget($cacheKey);
                    $node->update(['status' => 0]);
                    $data[$node_id]['message'] = '自动进入维护状态';
                }
            }

            if (! empty($data[$node_id])) {
                $data[$node_id]['name'] = $node->name;
            }
        }

        if (! empty($data)) { //只有在出现阻断线路时，才会发出警报
            Notification::send(User::find(1), new NodeBlocked($data));

            Log::notice("节点状态日志: \r\n".var_export($data, true));
        }

        Cache::put('LastCheckTime', time() + random_int(3000, Hour), 3700); // 随机生成下次检测时间

        $this->reliveNode();
    }

    private function reliveNode(): void
    {
        $onlineNode = NodeHeartbeat::recently()->distinct()->pluck('node_id');
        foreach (Node::whereRelayNodeId(null)->whereStatus(0)->whereIn('id', $onlineNode)->where('detection_type', '<>', 0)->get() as $node) {
            $ips = $node->ips();
            $reachableIPs = 0;

            foreach ($ips as $ip) {
                if ($node->detection_type) {
                    $status = (new NetworkDetection)->networkStatus($ip, $node->port ?? 22);

                    if (($node->detection_type === 1 && $status['tcp'] === 1) || ($node->detection_type === 2 && $status['icmp'] === 1) || ($status['tcp'] === 1 && $status['icmp'] === 1)) {
                        $reachableIPs++;
                    }

                    sleep(1);
                }
            }

            if ($reachableIPs === count($ips)) {
                $node->update(['status' => 1]);
            }
        }
    }
}
