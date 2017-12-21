<?php

namespace App\Console\Commands;

use App\Http\Models\Order;
use Illuminate\Console\Command;
use App\Http\Models\Config;
use App\Http\Models\User;
use Log;

class AutoResetUserTrafficJob extends Command
{
    protected $signature = 'command:autoResetUserTrafficJob';
    protected $description = '自动重置用户可用流量';
    protected static $config;

    public function __construct()
    {
        parent::__construct();

        $config = Config::query()->get();
        $data = [];
        foreach ($config as $vo) {
            $data[$vo->name] = $vo->value;
        }

        self::$config = $data;
    }

    public function handle()
    {
        if (self::$config['reset_traffic']) {
            $userList = User::query()->where('status', '>=', 0)->where('enable', 1)->get();
            foreach ($userList as $user) {
                if (!$user->traffic_reset_day) {
                    continue;
                }

                // 取出用户最后购买的有效套餐
                $order = Order::query()->with(['user', 'goods'])->whereHas('goods', function ($q) { $q->where('type', 2); })->where('user_id', $user->id)->where('is_expire', 0)->orderBy('oid', 'desc')->first();
                if (!$order) {
                    continue;
                }

                $today = abs(date('d'));
                $reset_days = [$today, 29, 30, 31];
                if (in_array($order->user->traffic_reset_day, $reset_days)) {
                    // 跳过本月，防止异常重置
                    if (date('m') == date('m', strtotime($order->expire_at))) {
                        continue;
                    }

                    if (date('m') == date('m', strtotime($order->created_at))) {
                        continue;
                    }

                    User::query()->where('id', $user->id)->update(['u' => 0, 'd' => 0]);
                }
            }
        }

        Log::info('定时任务：' . $this->description);
    }
}
