<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\Config;
use App\Http\Models\Order;
use App\Http\Models\User;
use App\Http\Models\UserLabel;
use App\Http\Models\GoodsLabel;

use Log;

class AutoDecGoodsTraffic extends Command
{
    protected $signature = 'autoDecGoodsTraffic';
    protected $description = '自动扣减用户到期流量包的流量';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $jobStartTime = microtime(true);

        $orderList = Order::query()->with(['user', 'goods'])->where('status', 2)->where('is_expire', 0)->where('expire_at', '<=', date('Y-m-d H:i:s'))->get();
        if (!$orderList->isEmpty()) {
            $config = $this->systemConfig();

            // 用户默认标签
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

                    // 取出用户的其他商品带有的标签
                    $goodsIds = Order::query()->where('user_id', $order->user->id)->where('oid', '<>', $order->oid)->where('status', 2)->where('is_expire', 0)->groupBy('goods_id')->pluck('goods_id')->toArray();
                    $goodsLabels = GoodsLabel::query()->whereIn('goods_id', $goodsIds)->groupBy('label_id')->pluck('label_id')->toArray();

                    // 标签去重
                    $labels = array_merge($goodsLabels, $defaultLabels);
                    $labels = array_unique($labels);
                    $labels = array_values($labels);
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
}
