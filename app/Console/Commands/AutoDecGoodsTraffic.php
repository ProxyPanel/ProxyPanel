<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use Illuminate\Console\Command;
use App\Http\Models\Order;
use App\Http\Models\User;
use App\Http\Models\UserLabel;
use App\Http\Models\GoodsLabel;
use Log;
use DB;

class AutoDecGoodsTraffic extends Command
{
    protected $signature = 'autoDecGoodsTraffic';
    protected $description = '自动扣减用户到期商品的流量';
    protected static $systemConfig;

    public function __construct()
    {
        parent::__construct();
        self::$systemConfig = Helpers::systemConfig();
    }

    public function handle()
    {
        $jobStartTime = microtime(true);

        // 扣减用户到期商品的流量
        $this->decGoodsTraffic();

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('执行定时任务【' . $this->description . '】，耗时' . $jobUsedTime . '秒');
    }

    // 扣减用户到期商品的流量
    private function decGoodsTraffic()
    {
        $orderList = Order::query()->with(['user', 'goods'])->where('status', 2)->where('is_expire', 0)->where('expire_at', '<', date('Y-m-d H:i:s'))->get();
        if (!$orderList->isEmpty()) {
            // 用户默认标签
            $defaultLabels = [];
            if (self::$systemConfig['initial_labels_for_user']) {
                $defaultLabels = explode(',', self::$systemConfig['initial_labels_for_user']);
            }

            DB::beginTransaction();
            try {
                foreach ($orderList as $order) {
                    // 先过期本订单
                    Order::query()->where('oid', $order->oid)->update(['is_expire' => 1]);

                    // 再检查该订单对应用户是否还有套餐（非流量包）存在
                    $haveOrder = Order::query()
                        ->with(['user', 'goods'])
                        ->where('is_expire', 0)
                        ->where('user_id', $order->user_id)
                        ->whereHas('goods', function ($q) {
                            $q->where('type', 2);
                        })
                        ->orderBy('oid', 'desc')
                        ->first();
                    if (!$haveOrder) {
                        // 如果不存在有效套餐（非流量包），则清空用户重置日
                        User::query()->where('id', $order->user_id)->update(['traffic_reset_day' => 0]);
                    }

                    if (empty($order->user) || empty($order->goods)) {
                        continue;
                    }

                    if ($order->user->transfer_enable - $order->goods->traffic * 1048576 <= 0) {
                        // 写入用户流量变动记录
                        Helpers::addUserTrafficModifyLog($order->user_id, $order->oid, $order->user->transfer_enable, 0, '[定时任务]用户所购商品到期，扣减商品对应的流量(扣完并重置)');

                        User::query()->where('id', $order->user_id)->update(['u' => 0, 'd' => 0, 'transfer_enable' => 0]);
                    } else {
                        // 写入用户流量变动记录
                        Helpers::addUserTrafficModifyLog($order->user_id, $order->oid, $order->user->transfer_enable, ($order->user->transfer_enable - $order->goods->traffic * 1048576), '[定时任务]用户所购商品到期，扣减商品对应的流量(没扣完)');

                        User::query()->where('id', $order->user_id)->decrement('transfer_enable', $order->goods->traffic * 1048576);

                        // 处理已用流量
                        if ($order->user->u + $order->user->d - $order->goods->traffic * 1048576 <= 0) {
                            User::query()->where('id', $order->user_id)->update(['u' => 0, 'd' => 0]);
                        } else {
                            // 一般来说d的值远远大于u
                            if ($order->user->d - $order->goods->traffic * 1048576 >= 0) {
                                User::query()->where('id', $order->user_id)->decrement('d', $order->goods->traffic * 1048576);
                            } else { // 如果d不够减，则减u，然后d置0
                                User::query()->where('id', $order->user_id)->decrement('u', $order->goods->traffic * 1048576 - $order->user->d);
                                User::query()->where('id', $order->user_id)->update(['d' => 0]);
                            }
                        }
                    }

                    // 删除该商品对应用户的所有标签
                    UserLabel::query()->where('user_id', $order->user->id)->delete();

                    // 取出用户的其他商品带有的标签
                    $goodsIds = Order::query()->where('user_id', $order->user->id)->where('oid', '<>', $order->oid)->where('status', 2)->where('is_expire', 0)->groupBy('goods_id')->pluck('goods_id')->toArray();
                    $goodsLabels = GoodsLabel::query()->whereIn('goods_id', $goodsIds)->groupBy('label_id')->pluck('label_id')->toArray();

                    // 生成标签
                    $labels = array_values(array_unique(array_merge($goodsLabels, $defaultLabels))); // 标签去重
                    foreach ($labels as $vo) {
                        $userLabel = new UserLabel();
                        $userLabel->user_id = $order->user->id;
                        $userLabel->label_id = $vo;
                        $userLabel->save();
                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                \Log::error($this->description . '：' . $e);

                DB::rollBack();
            }
        }
    }
}
