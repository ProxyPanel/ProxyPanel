<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\Coupon;
use App\Http\Models\CouponLog;
use App\Http\Models\Invite;
use App\Http\Models\Order;
use App\Http\Models\Payment;
use App\Http\Models\Config;
use App\Http\Models\User;
use App\Http\Models\UserLabel;
use App\Http\Models\UserBanLog;
use App\Http\Models\UserSubscribe;
use App\Http\Models\UserSubscribeLog;
use App\Http\Models\UserTrafficHourly;
use Log;
use DB;

class AutoJob extends Command
{
    protected $signature = 'autoJob';
    protected $description = '自动化任务';
    protected static $config;

    public function __construct()
    {
        parent::__construct();
        self::$config = $this->systemConfig();
    }

    /*
     * 以下操作顺序如果随意挪动可能导致出现异常
     */
    public function handle()
    {
        $jobStartTime = microtime(true);

        // 优惠券到期自动置无效
        $this->expireCoupon();

        // 邀请码到期自动置无效
        $this->expireInvite();

        // 封禁访问异常的订阅链接
        $this->blockSubscribe();

        // 封禁账号
        $this->blockUsers();

        // 自动移除被封禁账号的标签
        $this->removeUserLabels();

        // 自动解封被封禁的账号
        $this->unblockUsers();

        // 端口回收与分配
        $this->dispatchPort();

        // 关闭超时未支付订单
        $this->closeOrder();

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('执行定时任务【' . $this->description . '】，耗时' . $jobUsedTime . '秒');
    }

    // 优惠券到期自动置无效
    private function expireCoupon()
    {
        $couponList = Coupon::query()->where('status', 0)->where('available_end', '<=', time())->get();
        if (!$couponList->isEmpty()) {
            foreach ($couponList as $coupon) {
                Coupon::query()->where('id', $coupon->id)->update(['status' => 2]);
            }
        }
    }

    // 邀请码到期自动置无效
    private function expireInvite()
    {
        $inviteList = Invite::query()->where('status', 0)->where('dateline', '<=', date('Y-m-d H:i:s'))->get();
        if (!$inviteList->isEmpty()) {
            foreach ($inviteList as $invite) {
                Invite::query()->where('id', $invite->id)->update(['status' => 2]);
            }
        }
    }

    // 封禁访问异常的订阅链接
    private function blockSubscribe()
    {
        if (self::$config['is_subscribe_ban']) {
            $subscribeList = UserSubscribe::query()->where('status', 1)->get();
            if (!$subscribeList->isEmpty()) {
                foreach ($subscribeList as $subscribe) {
                    // 24小时内不同IP的请求次数
                    $request_times = UserSubscribeLog::query()->where('sid', $subscribe->id)->where('request_time', '>=', date("Y-m-d H:i:s", strtotime("-24 hours")))->distinct('request_ip')->count('request_ip');
                    if ($request_times >= self::$config['subscribe_ban_times']) {
                        UserSubscribe::query()->where('id', $subscribe->id)->update(['status' => 0, 'ban_time' => time(), 'ban_desc' => '存在异常，自动封禁']);

                        // 记录封禁日志
                        $this->addUserBanLog($subscribe->user_id, 0, '【完全封禁订阅】-订阅24小时内请求异常');
                    }
                }
            }
        }
    }

    // 封禁账号
    private function blockUsers()
    {
        // 过期用户处理
        $userList = User::query()->where('status', '>=', 0)->where('enable', 1)->where('expire_time', '<=', date('Y-m-d'))->get();
        if (!$userList->isEmpty()) {
            foreach ($userList as $user) {
                if (self::$config['is_ban_status']) {
                    User::query()->where('id', $user->id)->update([
                        'u'                 => 0,
                        'd'                 => 0,
                        'transfer_enable'   => 0,
                        'enable'            => 0,
                        'traffic_reset_day' => 0,
                        'ban_time'          => 0,
                        'status'            => -1
                    ]);

                    $this->addUserBanLog($user->id, 0, '【禁止登录，清空账户】-账号已过期');
                } else {
                    User::query()->where('id', $user->id)->update([
                        'u'                 => 0,
                        'd'                 => 0,
                        'transfer_enable'   => 0,
                        'enable'            => 0,
                        'traffic_reset_day' => 0,
                        'ban_time'          => 0
                    ]);

                    $this->addUserBanLog($user->id, 0, '【封禁代理，清空账户】-账号已过期');
                }
            }
        }

        // 封禁1小时内流量异常账号
        if (self::$config['is_traffic_ban']) {
            $userList = User::query()->where('status', '>=', 0)->where('enable', 1)->where('ban_time', 0)->get();
            if (!$userList->isEmpty()) {
                foreach ($userList as $user) {
                    // 多往前取5分钟，防止数据统计任务执行时间过长导致没有数据
                    $totalTraffic = UserTrafficHourly::query()->where('user_id', $user->id)->where('node_id', 0)->where('created_at', '>=', date('Y-m-d H:i:s', time() - 3900))->sum('total');
                    if ($totalTraffic >= (self::$config['traffic_ban_value'] * 1024 * 1024 * 1024)) {
                        User::query()->where('id', $user->id)->update(['enable' => 0, 'ban_time' => strtotime(date('Y-m-d H:i:s', strtotime("+" . self::$config['traffic_ban_time'] . " minutes")))]);

                        // 写入日志
                        $this->addUserBanLog($user->id, self::$config['traffic_ban_time'], '【临时封禁代理】-24小时内流量异常');
                    }
                }
            }
        }

        // 禁用流量超限用户
        $userList = User::query()->where('status', '>=', 0)->where('enable', 1)->where('ban_time', 0)->whereRaw("u + d >= transfer_enable")->get();
        if (!$userList->isEmpty()) {
            foreach ($userList as $user) {
                User::query()->where('id', $user->id)->update(['enable' => 0]);

                // 写入日志
                $this->addUserBanLog($user->id, 0, '【封禁代理】-流量已用完');
            }
        }
    }

    // 自动清空过期的账号的标签和流量（临时封禁不移除）
    private function removeUserLabels()
    {
        $userList = User::query()->where('enable', 0)->where('ban_time', 0)->where('expire_time', '<=', date('Y-m-d'))->get();
        if (!$userList->isEmpty()) {
            foreach ($userList as $user) {
                UserLabel::query()->where('user_id', $user->id)->delete();
                User::query()->where('id', $user->id)->update([
                    'u'                 => 0,
                    'd'                 => 0,
                    'transfer_enable'   => 0,
                    'traffic_reset_day' => 0
                ]);
            }
        }
    }

    // 解封账号
    private function unblockUsers()
    {
        // 解封被临时封禁的账号
        $userList = User::query()->where('status', '>=', 0)->where('enable', 0)->where('ban_time', '>', 0)->get();
        foreach ($userList as $user) {
            if ($user->ban_time < time()) {
                User::query()->where('id', $user->id)->update(['enable' => 1, 'ban_time' => 0]);

                // 写入操作日志
                $this->addUserBanLog($user->id, 0, '【自动解封】-临时封禁到期');
            }
        }

        // 可用流量大于已用流量也解封（比如：邀请返利自动加了流量）
        $userList = User::query()->where('status', '>=', 0)->where('enable', 0)->where('ban_time', 0)->where('expire_time', '>', date('Y-m-d'))->whereRaw("u + d < transfer_enable")->get();
        if (!$userList->isEmpty()) {
            foreach ($userList as $user) {
                User::query()->where('id', $user->id)->update(['enable' => 1]);

                // 写入操作日志
                $this->addUserBanLog($user->id, 0, '【自动解封】-有流量解封');
            }
        }
    }

    // 端口回收与分配
    private function dispatchPort()
    {
        // 自动分配端口
        if (self::$config['auto_release_port']) {
            $userList = User::query()->where('status', '>=', 0)->where('enable', 1)->where('port', 0)->get();
            if (!$userList->isEmpty()) {
                foreach ($userList as $user) {
                    $port = self::$config['is_rand_port'] ? $this->getRandPort() : $this->getOnlyPort();

                    User::query()->where('id', $user->id)->update(['port' => $port]);
                }
            }
        }

        // 被封禁的账号自动释放端口
        if (self::$config['auto_release_port']) {
            $userList = User::query()->where('status', -1)->where('enable', 0)->get();
            if (!$userList->isEmpty()) {
                foreach ($userList as $user) {
                    if ($user->port) {
                        User::query()->where('id', $user->id)->update(['port' => 0]);
                    }
                }
            }
        }
    }

    // 自动关闭超时未支付订单
    private function closeOrder()
    {
        // 自动关闭超时未支付的有赞云订单（有赞云收款二维码超过60分钟自动关闭，我们限制30分钟内必须付款）
        $paymentList = Payment::query()->with(['order', 'order.coupon'])->where('status', 0)->where('created_at', '<=', date("Y-m-d H:i:s", strtotime("-30 minutes")))->get();
        if (!$paymentList->isEmpty()) {
            DB::beginTransaction();
            try {
                foreach ($paymentList as $payment) {
                    // 关闭支付单
                    Payment::query()->where('id', $payment->id)->update(['status' => -1]);

                    // 关闭订单
                    Order::query()->where('oid', $payment->oid)->update(['status' => -1]);

                    // 退回优惠券
                    if ($payment->order->coupon_id) {
                        Coupon::query()->where('id', $payment->order->coupon_id)->update(['status' => 0]);

                        $this->addCouponLog($payment->order->coupon_id, $payment->order->goods_id, $payment->oid, '订单超时未支付，自动退回');
                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                Log::info('【异常】自动关闭超时未支付订单：' . $e->getMessage());

                DB::rollBack();
            }
        }
    }

    /**
     * 添加用户封禁日志
     *
     * @param int    $userId  用户ID
     * @param int    $minutes 封禁时长，单位分钟
     * @param string $desc    封禁理由
     */
    private function addUserBanLog($userId, $minutes, $desc)
    {
        $log = new UserBanLog();
        $log->user_id = $userId;
        $log->minutes = $minutes;
        $log->desc = $desc;
        $log->save();
    }

    /**
     * 添加优惠券操作日志
     *
     * @param int    $couponId 优惠券ID
     * @param int    $goodsId  商品ID
     * @param int    $orderId  订单ID
     * @param string $desc     备注
     */
    private function addCouponLog($couponId, $goodsId, $orderId, $desc = '')
    {
        $couponLog = new CouponLog();
        $couponLog->coupon_id = $couponId;
        $couponLog->goods_id = $goodsId;
        $couponLog->order_id = $orderId;
        $couponLog->desc = $desc;
        $couponLog->save();
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

    // 获取一个随机端口
    public function getRandPort()
    {
        $config = $this->systemConfig();

        $port = mt_rand($config['min_port'], $config['max_port']);
        $deny_port = [1068, 1109, 1434, 3127, 3128, 3129, 3130, 3332, 4444, 5554, 6669, 8080, 8081, 8082, 8181, 8282, 9996, 17185, 24554, 35601, 60177, 60179]; // 不生成的端口

        $exists_port = User::query()->pluck('port')->toArray();
        if (in_array($port, $exists_port) || in_array($port, $deny_port)) {
            $port = $this->getRandPort();
        }

        return $port;
    }

    // 获取一个端口
    public function getOnlyPort()
    {
        $config = $this->systemConfig();

        $port = $config['min_port'];
        $deny_port = [1068, 1109, 1434, 3127, 3128, 3129, 3130, 3332, 4444, 5554, 6669, 8080, 8081, 8082, 8181, 8282, 9996, 17185, 24554, 35601, 60177, 60179]; // 不生成的端口

        $exists_port = User::query()->where('port', '>=', $config['min_port'])->where('port', '<=', $config['max_port'])->pluck('port')->toArray();
        while (in_array($port, $exists_port) || in_array($port, $deny_port)) {
            $port = $port + 1;
        }

        return $port;
    }
}
