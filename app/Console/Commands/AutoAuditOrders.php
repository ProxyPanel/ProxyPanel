<?php

namespace App\Console\Commands;

use App\Components\Yzy;
use App\Http\Models\Config;
use App\Http\Models\Goods;
use App\Http\Models\GoodsLabel;
use App\Http\Models\Order;
use App\Http\Models\Payment;
use App\Http\Models\ReferralLog;
use App\Http\Models\User;
use App\Http\Models\UserLabel;
use Illuminate\Console\Command;
use Log;
use DB;

class AutoAuditOrders extends Command
{
    protected $signature = 'autoAuditOrders';
    protected $description = '自动审计待支付订单';
    protected static $config;

    public function __construct()
    {
        parent::__construct();
        self::$config = $this->systemConfig();
    }

    /*
     * 因为订单在15分钟未支付则会被自动关闭
     * 当有赞没有正常推送消息或者其他原因导致用户已付款但是订单不生效从而导致用户无法正常加流量、置状态
     * 故需要每分钟请求一次未支付订单，审计一下其支付状态
     */
    public function handle()
    {
        $jobStartTime = microtime(true);

        // 审计待支付的订单
        $this->auditOrders();

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('执行定时任务【' . $this->description . '】，耗时' . $jobUsedTime . '秒');
    }

    // 审计待支付的订单
    private function auditOrders()
    {
        $paymentList = Payment::query()->with(['order', 'user'])->where('status', 0)->get();
        if (!$paymentList->isEmpty()) {
            foreach ($paymentList as $payment) {
                // 跳过order丢失的订单
                if (!isset($payment->order)) {
                    continue;
                }

                $yzy = new yzy();
                $trade = $yzy->getTradeByQrId($payment->qr_id);
                if ($trade['response']['total_results']) {
                    // 再判断一遍当前要操作的订单的状态是否被改变了（可能请求延迟的时候已经回调处理完了）
                    $payment = Payment::query()->where('id', $payment->id)->first();
                    if ($payment->status != '0') {
                        continue;
                    }

                    // 处理订单
                    DB::beginTransaction();
                    try {
                        // 更新支付单
                        $payment->pay_way = $trade['response']['qr_trades']['pay_type'] == 'WXPAY_BIGUNSIGN' ? 1 : 2; // 1-微信、2-支付宝
                        $payment->status = 1;
                        $payment->save();

                        // 更新订单
                        $order = Order::query()->with(['user'])->where('oid', $payment->oid)->first();
                        $order->status = 2;
                        $order->save();

                        // 如果买的是套餐，则先将之前购买的所有套餐置都无效，并扣掉之前所有套餐的流量
                        $goods = Goods::query()->where('id', $order->goods_id)->first();
                        if ($goods->type == 2) {
                            $existOrderList = Order::query()
                                ->with(['goods'])
                                ->whereHas('goods', function ($q) {
                                    $q->where('type', 2);
                                })
                                ->where('user_id', $order->user_id)
                                ->where('oid', '<>', $order->oid)
                                ->where('is_expire', 0)
                                ->where('status', 2)
                                ->get();

                            foreach ($existOrderList as $vo) {
                                Order::query()->where('oid', $vo->oid)->update(['is_expire' => 1]);
                                User::query()->where('id', $order->user_id)->decrement('transfer_enable', $vo->goods->traffic * 1048576);
                            }
                        }

                        // 把商品的流量加到账号上
                        User::query()->where('id', $order->user_id)->increment('transfer_enable', $goods->traffic * 1048576);

                        // 计算账号过期时间
                        if ($order->user->expire_time < date('Y-m-d', strtotime("+" . $goods->days . " days"))) {
                            $expireTime = date('Y-m-d', strtotime("+" . $goods->days . " days"));
                        } else {
                            $expireTime = $order->user->expire_time;
                        }

                        // 套餐就改流量重置日，流量包不改
                        if ($goods->type == 2) {
                            if (date('m') == 2 && date('d') == 29) {
                                $traffic_reset_day = 28;
                            } else {
                                $traffic_reset_day = date('d') == 31 ? 30 : abs(date('d'));
                            }
                            User::query()->where('id', $order->user_id)->update(['traffic_reset_day' => $traffic_reset_day, 'expire_time' => $expireTime, 'enable' => 1]);
                        } else {
                            User::query()->where('id', $order->user_id)->update(['expire_time' => $expireTime, 'enable' => 1]);
                        }

                        // 写入用户标签
                        if ($goods->label) {
                            // 用户默认标签
                            $defaultLabels = [];
                            if (self::$config['initial_labels_for_user']) {
                                $defaultLabels = explode(',', self::$config['initial_labels_for_user']);
                            }

                            // 取出现有的标签
                            $userLabels = UserLabel::query()->where('user_id', $order->user_id)->pluck('label_id')->toArray();
                            $goodsLabels = GoodsLabel::query()->where('goods_id', $order->goods_id)->pluck('label_id')->toArray();

                            // 标签去重
                            $newUserLabels = array_values(array_unique(array_merge($userLabels, $goodsLabels, $defaultLabels)));

                            // 删除用户所有标签
                            UserLabel::query()->where('user_id', $order->user_id)->delete();

                            // 生成标签
                            foreach ($newUserLabels as $vo) {
                                $obj = new UserLabel();
                                $obj->user_id = $order->user_id;
                                $obj->label_id = $vo;
                                $obj->save();
                            }
                        }

                        // 写入返利日志
                        if ($order->user->referral_uid) {
                            $this->addReferralLog($order->user_id, $order->user->referral_uid, $order->oid, $order->amount, $order->amount * self::$config['referral_percent']);
                        }

                        // 取消重复返利
                        User::query()->where('id', $order->user_id)->update(['referral_uid' => 0]);

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();

                        Log::info('【有赞云】审计订单时更新支付单和订单异常：' . $e->getMessage());
                    }
                }
            }
        }
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

    // 添加返利日志
    public function addReferralLog($userId, $refUserId, $oid, $amount, $refAmount)
    {
        $log = new ReferralLog();
        $log->user_id = $userId;
        $log->ref_user_id = $refUserId;
        $log->order_id = $oid;
        $log->amount = $amount;
        $log->ref_amount = $refAmount;
        $log->status = 0;

        return $log->save();
    }
}
