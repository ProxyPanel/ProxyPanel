<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\SsNode;
use App\Http\Models\SsNodeTrafficHourly;
use App\Http\Models\UserTrafficLog;
use Log;

class AutoStatisticsNodeHourlyTrafficJob extends Command
{
    protected $signature = 'autoStatisticsNodeHourlyTrafficJob';
    protected $description = '自动统计节点每小时流量';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $nodeList = SsNode::query()->where('status', 1)->orderBy('id', 'asc')->get();
        foreach ($nodeList as $node) {
            $this->statisticsByNode($node->id);
        }

        Log::info('定时任务：' . $this->description);
    }

    private function statisticsByNode($node_id)
    {
        $start_time = strtotime(date('Y-m-d H:i:s', strtotime("-1 hour")));
        $end_time = time();

        $query = UserTrafficLog::query()->where('node_id', $node_id)->whereBetween('log_time', [$start_time, $end_time]);

        $u = $query->sum('u');
        $d = $query->sum('d');
        $total = $u + $d;
        $traffic = $this->flowAutoShow($total);

        $obj = new SsNodeTrafficHourly();
        $obj->node_id = $node_id;
        $obj->u = $u;
        $obj->d = $d;
        $obj->total = $total;
        $obj->traffic = $traffic;
        $obj->save();
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
