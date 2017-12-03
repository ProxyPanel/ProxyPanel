<?php

namespace App\Console\Commands;

use App\Http\Models\OrderGoods;
use Illuminate\Console\Command;
use App\Http\Models\Config;
use App\Http\Models\User;
use Log;

class AutoResetUserTrafficJob extends Command
{
    protected $signature = 'command:autoResetUserTrafficJob';
    protected $description = '自动重置用户流量';

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
                if (empty($user->traffic_reset_day)) {
                    continue;
                }

                // 取出这个用户最后购买的有效套餐
                $orderGoods = OrderGoods::query()->with(['goods' => function($q) { $q->where('type', 2); }])->where('user_id', $user->id)->where('is_expire', 0)->orderBy('id', 'desc')->first();
                if (empty($orderGoods) || empty($orderGoods->goods)) {
                    continue;
                }

                if ($user->traffic_reset_day == abs(date('d')) && date('m') == date('m', strtotime($orderGoods->created_at))) {
                    continue;
                }

                User::query()->where('id', $user->id)->update(['u' => 0, 'd' => 0]);
            }
        }

        Log::info('定时任务：' . $this->description);
    }
}
