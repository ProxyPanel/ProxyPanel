<?php

namespace App\Console\Commands;

use App\Components\ServerChan;
use Illuminate\Console\Command;
use App\Http\Models\Config;
use App\Http\Models\User;
use App\Http\Models\UserTrafficHourly;
use Log;

class UserTrafficAbnormalAutoWarning extends Command
{
    protected $signature = 'userTrafficAbnormalAutoWarning';
    protected $description = '用户流量异常警告';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $jobStartTime = microtime(true);

        // 1小时内流量异常用户(多往前取5分钟，防止数据统计任务执行时间过长导致没有数据)
        $userTotalTrafficList = UserTrafficHourly::query()->where('node_id', 0)->where('total', '>', 104857600)->where('created_at', '>=', date('Y-m-d H:i:s', time() - 3900))->groupBy('user_id')->selectRaw("user_id, sum(total) as totalTraffic")->get(); // 只统计100M以上的记录，加快查询速度
        if (!$userTotalTrafficList->isEmpty()) {
            $title = "流量异常用户提醒";
            $config = $this->systemConfig();

            foreach ($userTotalTrafficList as $vo) {
                $user = User::query()->where('id', $vo->user_id)->first();

                // 通过ServerChan发微信消息提醒管理员
                if ($vo->totalTraffic > ($config['traffic_ban_value'] * 1024 * 1024 * 1024) && $config['is_server_chan'] && $config['server_chan_key']) {
                    $traffic = UserTrafficHourly::query()->where('node_id', 0)->where('user_id', $vo->user_id)->where('created_at', '>=', date('Y-m-d H:i:s', time() - 3900))->selectRaw("user_id, sum(`u`) as totalU, sum(`d`) as totalD, sum(total) as totalTraffic")->first();

                    $content = "用户**{$user->username}(ID:{$user->id})**，最近1小时**上传流量：{$this->flowAutoShow($traffic->totalU)}，下载流量：{$this->flowAutoShow($traffic->totalD)}，共计：{$this->flowAutoShow($traffic->totalTraffic)}**。";

                    $serverChan = new ServerChan();
                    $serverChan->send($title, $content);
                }
            }
        }

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('执行定时任务【' . $this->description . '】，耗时' . $jobUsedTime . '秒');
    }

    // 系统配置
    private function systemConfig()
    {
        $config = Config::query()->get();
        $data = [];
        foreach ($config as $vo) {
            $data[$vo->name] = $vo->value;
        }

        return $data;
    }

    // 根据流量值自动转换单位输出
    private function flowAutoShow($value = 0)
    {
        $kb = 1024;
        $mb = 1048576;
        $gb = 1073741824;
        $tb = $gb * 1024;
        $pb = $tb * 1024;
        if (abs($value) > $pb) {
            return round($value / $pb, 2) . "PB";
        } elseif (abs($value) > $tb) {
            return round($value / $tb, 2) . "TB";
        } elseif (abs($value) > $gb) {
            return round($value / $gb, 2) . "GB";
        } elseif (abs($value) > $mb) {
            return round($value / $mb, 2) . "MB";
        } elseif (abs($value) > $kb) {
            return round($value / $kb, 2) . "KB";
        } else {
            return round($value, 2) . "B";
        }
    }
}
