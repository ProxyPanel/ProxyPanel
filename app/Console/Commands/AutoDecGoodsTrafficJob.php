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
    protected $description = '商品到期自动扣购买该商品的账号流量';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $goodsList = Goods::where('end_time', '<', date('Y-m-d H:i:s'))->get();
        foreach ($goodsList as $goods) {
            // 所有购买过该商品的用户
            $orderGoods = OrderGoods::where('goods_id', $goods->id)->get();
            foreach ($orderGoods as $og) {
                $u = User::where('id', $og->user_id)->first();
                if (empty($u)) {
                    continue;
                }

                if ($u->transfer_enable - $goods->traffic * 1024 * 1024 < 0) {
                    User::where('id', $og->user_id)->update(['transfer_enable' => 0]);
                } else {
                    User::where('id', $og->user_id)->decrement('transfer_enable', $goods->traffic * 1024 * 1024);
                }
            }
        }

        Log::info('定时任务：' . $this->description);
    }
}
