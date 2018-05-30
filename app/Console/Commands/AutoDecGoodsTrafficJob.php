<?php

namespace App\Console\Commands;

use App\Http\Models\Config;
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
            // 用户默认标签
            $config = $this->systemConfig();
            $defaultLabels = [];
            if ($config['initial_labels_for_user']) {
                $defaultLabels = explode(',', $config['initial_labels_for_user']);
            }

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

                    // 取出用户的全部其他商品并打上对应的标签
                    $goodsIds = Order::query()->where('user_id', $order->user->id)->where('oid', '<>', $order->oid)->where('is_expire', 0)->groupBy('goods_id')->pluck('goods_id')->toArray();
                    $goodsLabels = GoodsLabel::query()->whereIn('goods_id', $goodsIds)->groupBy('label_id')->pluck('label_id')->toArray();

                    // 合并默认标签
                    $labels = $defaultLabels ? array_merge($goodsLabels, $defaultLabels) : $goodsLabels;
                    foreach ($labels as $vo) {
                        $userLabel = new UserLabel();
                        $userLabel->user_id = $order->user->id;
                        $userLabel->label_id = $vo;
                        $userLabel->save();
                    }


                    Order::query()->where('oid', $order->oid)->update(['is_expire' => 1]);
                }
            }
        }

        Log::info('定时任务：' . $this->description);
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
}
