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
        $jobStartTime = microtime(true);

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

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info("---【{$this->description}】完成---，耗时 {$jobUsedTime} 秒");
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
                'ip'   => $node->ip,
            ];
        }

        if (isset($data)) {
            Notification::send(User::find(1), new NodeOffline($data));
        }
    }

    private function checkNodeNetwork(): void
    {
        $detectionCheckTimes = sysConfig('detection_check_times');
        $sendText = false;
        $message = "| 线路 | 协议 | 状态 |\r\n| ------ | ------ | ------ |\r\n";
        $additionalMessage = '';
        foreach (Node::whereIsRelay(0)->whereStatus(1)->where('detection_type', '>', 0)->get() as $node) {
            $info = false;
            if ($node->detection_type === 0) {
                continue;
            }
            // 使用DDNS的node先通过gethostbyname获取ipv4地址
            if ($node->is_ddns) {
                $ip = gethostbyname($node->server);
                if (strcmp($ip, $node->server) !== 0) {
                    $node->ip = $ip;
                } else {
                    Log::warning('【节点阻断检测】检测'.$node->server.'时，IP获取失败'.$ip.' | '.$node->server);
                }
            }
            if ($node->detection_type !== 1) {
                $icmpCheck = (new NetworkDetection)->networkCheck($node->ip, true);
                if ($icmpCheck !== false && $icmpCheck !== '通讯正常') {
                    $message .= '| '.$node->name.' | ICMP | '.$icmpCheck." |\r\n";
                    $sendText = true;
                    $info = true;
                }
            }
            if ($node->detection_type !== 2) {
                $tcpCheck = (new NetworkDetection)->networkCheck($node->ip, false, $node->single ? $node->port : 22);
                if ($tcpCheck !== false && $tcpCheck !== '通讯正常') {
                    $message .= '| '.$node->name.' | TCP | '.$tcpCheck." |\r\n";
                    $sendText = true;
                    $info = true;
                }
            }
            sleep(5);

            // 节点检测次数
            if ($info && $detectionCheckTimes) {
                // 已通知次数
                $cacheKey = 'detection_check_times'.$node->id;
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
                    $additionalMessage .= "\r\n节点【{$node->name}】自动进入维护状态\r\n";
                }
            }
        }

        if ($sendText) {//只有在出现阻断线路时，才会发出警报
            Notification::send(User::find(1), new NodeBlocked($message.$additionalMessage));

            Log::info("阻断日志: \r\n".$message.$additionalMessage);
        }

        Cache::put('LastCheckTime', time() + random_int(3000, Hour), 3700); // 随机生成下次检测时间
    }
}
