<?php

namespace App\Console\Commands;

use App\Http\Models\SsNodeInfo;
use App\Http\Models\SsNodeOnlineLog;
use App\Http\Models\SsNodeTrafficHourly;
use App\Http\Models\UserTrafficLog;
use Illuminate\Console\Command;
use App\Http\Models\Config;
use App\Http\Models\UserTrafficHourly;
use Log;

class AutoClearLogJob extends Command
{
    protected $signature = 'autoClearLogJob';
    protected $description = '自动清除日志';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $config = $this->systemConfig();

        if ($config['is_clear_log']) {
            // 自动清除10分钟以前的节点负载信息日志
            SsNodeInfo::query()->where('log_time', '<=', strtotime(date('Y-m-d H:i:s', strtotime("-10 minutes"))))->delete();

            // 自动清除1小时以前的节点负载信息日志
            SsNodeOnlineLog::query()->where('log_time', '<=', strtotime(date('Y-m-d H:i:s', strtotime("-60 minutes"))))->delete();

            // 自动清除30天以前的用户流量日志
            UserTrafficLog::query()->where('log_time', '<=', strtotime(date('Y-m-d H:i:s', strtotime("-30 days"))))->delete();

            // 自动清除10天以前的用户每小时流量数据日志
            UserTrafficHourly::query()->where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-10 days')))->delete();

            // 自动清除60天以前的节点每小时流量数据日志
            SsNodeTrafficHourly::query()->where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-60 days')))->delete();
        }

        Log::info('定时任务：' . $this->description);
    }

    // 系统配置
    private function systemConfig() {
        $config = Config::query()->get();
        $data = [];
        foreach ($config as $vo) {
            $data[$vo->name] = $vo->value;
        }

        return $data;
    }
}
