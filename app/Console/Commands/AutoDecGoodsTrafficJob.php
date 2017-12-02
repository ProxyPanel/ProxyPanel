<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\Goods;
use App\Http\Models\OrderGoods;
use App\Http\Models\User;
use Log;

class AutoDecGoodsTrafficJob extends Command
{
    protected $signature = 'command:autoDecGoodsTrafficJob';
    protected $description = '自动扣除到期流量包的流量';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $orderGoods = OrderGoods::query()->with(['user', 'goods'])->where('is_expire', 0)->get();
        foreach ($orderGoods as $og) {
            if (empty($og->goods) || $og->goods->is_del || empty($og->user)) {
                continue;
            }

            // 到期自动处理
            if (date("Y-m-d H:i:s", strtotime("-" . $og->goods->days . " days")) >= $og->created_at) {
                if ($og->user->transfer_enable - $og->traffic * 1048576 <= 0) {
                    User::query()->where('id', $og->user_id)->update(['transfer_enable' => 0]);
                } else {
                    User::query()->where('id', $og->user_id)->decrement('transfer_enable', $og->traffic * 1048576);
                }

                OrderGoods::query()->where('id', $og->id)->update(['is_expire' => 1]);
            }
        }

        Log::info('定时任务：' . $this->description);
    }
}
