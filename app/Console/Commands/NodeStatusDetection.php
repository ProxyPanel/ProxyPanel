<?php

namespace App\Console\Commands;

use App\Components\NetworkDetection;
use App\Models\Node;
use App\Models\NodeHeartbeat;
use App\Models\User;
use App\Notifications\NodeBlocked;
use App\Notifications\NodeOffline;
use Cache;
use Illuminate\Console\Command;
use Log;
use Notification;

class NodeStatusDetection extends Command
{
    protected $signature = 'nodeStatusDetection';
    protected $description = '节点状态检测';

    public function handle()
    {
        $jobTime = microtime(true);

        if (sysConfig('node_offline_notification')) {// 检测节点心跳是否异常
            $this->checkNodeStatus();
        }

        if (sysConfig('node_blocked_notification')) {// 监测节点网络状态
            if (! Cache::has('LastCheckTime') || Cache::get('LastCheckTime') <= time()) {
                $this->checkNodeNetwork();
            } else {
                Log::info('下次节点阻断检测时间：'.date('Y-m-d H:i:s', Cache::get('LastCheckTime')));
            }
        }

        $jobTime = round((microtime(true) - $jobTime), 4);

        Log::info("---【{$this->description}】完成---，耗时 {$jobTime} 秒");
    }

    private function checkNodeStatus()
    {
        $offlineCheckTimes = sysConfig('offline_check_times');
        $onlineNode = NodeHeartbeat::recently()->distinct()->pluck('node_id')->toArray();
        foreach (Node::whereIsRelay(0)->whereStatus(1)->whereNotIn('id', $onlineNode)->get() as $node) {
            // 近期无节点负载信息则认为是后端炸了
            if ($offlineCheckTimes) {
                // 已通知次数
                $cacheKey = 'offline_check_times'.$node->id;
                $times = 1;
                if (Cache::has($cacheKey)) {
                    $times = Cache::get($cacheKey);
                } else {
                    Cache::put($cacheKey, 1, Day); // 键将保留24小时
                }
                if ($times > $offlineCheckTimes) {
                    continue;
                }
                Cache::increment($cacheKey);
            }
            $data[] = [
                'name' => $node->name,
                'host' => $node->host,
            ];
        }

        if (isset($data)) {
            Notification::send(User::find(1), new NodeOffline($data));
        }
    }

    private function checkNodeNetwork(): void
    {
        $detectionCheckTimes = sysConfig('detection_check_times');

        foreach (Node::whereIsRelay(0)->whereStatus(1)->where('detection_type', '<>', 0)->get() as $node) {
            $node_id = $node->id;
            // 使用DDNS的node先通过gethostbyname获取ipv4地址
            foreach ($node->ips() as $ip) {
                if ($node->detection_type !== 1) {
                    $icmpCheck = (new NetworkDetection)->networkCheck($ip, true, $node->port ?? 22);
                    if ($icmpCheck !== false && $icmpCheck !== 1) {
                        $data[$node_id][$ip]['icmp'] = config('common.network_status')[$icmpCheck];
                    }
                }
                if ($node->detection_type !== 2) {
                    $tcpCheck = (new NetworkDetection)->networkCheck($ip, false, $node->port ?? 22);
                    if ($tcpCheck !== false && $tcpCheck !== 1) {
                        $data[$node_id][$ip]['tcp'] = config('common.network_status')[$tcpCheck];
                    }
                }
            }

            // 节点检测次数
            if (isset($data[$node_id]) && $detectionCheckTimes) {
                // 已通知次数
                $cacheKey = 'detection_check_times'.$node_id;
                if (Cache::has($cacheKey)) {
                    $times = Cache::get($cacheKey);
                } else {
                    // 键将保留12小时，多10分钟防意外
                    Cache::put($cacheKey, 1, 43800);
                    $times = 1;
                }

                if ($times < $detectionCheckTimes) {
                    Cache::increment($cacheKey);
                } else {
                    Cache::forget($cacheKey);
                    $node->update(['status' => 0]);
                    $data[$node_id]['message'] = '自动进入维护状态';
                }
            }

            if (isset($data[$node_id])) {
                $data[$node_id]['name'] = $node->name;
            }

            sleep(5);
        }

        if (isset($data)) { //只有在出现阻断线路时，才会发出警报
            Notification::send(User::find(1), new NodeBlocked($data));

            Log::notice("节点状态日志: \r\n".var_export($data, true));
        }

        Cache::put('LastCheckTime', time() + random_int(3000, Hour), 3700); // 随机生成下次检测时间
    }
}
