<?php

namespace App\Console\Commands;

use App\Http\Models\GoodsLabel;
use App\Http\Models\UserLabel;
use Illuminate\Console\Command;
use App\Http\Models\Order;
use App\Http\Models\User;
use Log;

class AutoDecGoodsTrafficJob extends Command
{
    protected $signature = 'autoDecGoodsTrafficJob';
    protected $description = '自动扣减用户到期流量包的流量';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $orderList = Order::query()->with(['user', 'goods'])->where('status', 2)->where('is_expire', 0)->get();
        if (!$orderList->isEmpty()) {
            foreach ($orderList as $order) {
                if (empty($order->user) || empty($order->goods)) {
                    continue;
                }

                // 到期自动处理
                if (date("Y-m-d H:i:s") >= $order->expire_at) {
                    if ($order->user->transfer_enable - $order->goods->traffic * 1048576 <= 0) {
                        User::query()->where('id', $order->user_id)->update(['transfer_enable' => 0]);
                    } else {
                        User::query()->where('id', $order->user_id)->decrement('transfer_enable', $order->goods->traffic * 1048576);
                    }

                    // 删除该商品对应用户的所有标签
                    UserLabel::query()->where('user_id', $order->user->id)->delete();

                    // 取出用户的全部其他商品
                    $goodsIds = Order::query()->where('user_id', $order->user->id)->where('oid', '<>', $order->oid)->groupBy('goods_id')->pluck('goods_id')->toArray();
                    $goodsLabels = GoodsLabel::query()->whereIn('goods_id', $goodsIds)->groupBy('label_id')->pluck('label_id')->toArray();
                    foreach ($goodsLabels as $label) {
                        $userLabel = new UserLabel();
                        $userLabel->user_id = $order->user->id;
                        $userLabel->label_id = $label;
                        $userLabel->save();
                    }

                    Order::query()->where('oid', $order->oid)->update(['is_expire' => 1]);
                }
            }
        }

        Log::info('定时任务：' . $this->description);
    }
}
