<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Components\PushNotification;
use App\Models\Config;
use App\Models\Coupon;
use App\Models\Invite;
use App\Models\Node;
use App\Models\NodeHeartbeat;
use App\Models\Order;
use App\Models\User;
use App\Models\VerifyCode;
use Cache;
use Illuminate\Console\Command;
use Log;

class AutoJob extends Command
{
    protected $signature = 'autoJob';
    protected $description = '自动化任务';

    /*
     * 警告：除非熟悉业务流程，否则不推荐更改以下执行顺序，随意变更以下顺序可能导致系统异常
     */
    public function handle(): void
    {
        $jobStartTime = microtime(true);

        // 关闭超时未支付本地订单
        Order::query()->recentUnPay()->update(['status' => -1]);

        //过期验证码、优惠券、邀请码无效化
        $this->expireCode();

        // 封禁访问异常的订阅链接
        $this->blockSubscribe();

        // 封禁账号
        $this->blockUsers();

        // 解封被封禁的账号
        $this->unblockUsers();

        // 端口回收与分配
        if (sysConfig('auto_release_port')) {
            $this->dispatchPort();
        }

        // 检测节点是否离线
        $this->checkNodeStatus();

        // 检查维护模式
        if (sysConfig('maintenance_mode') && sysConfig('maintenance_time') && sysConfig('maintenance_time') <= date('c')) {
            Config::whereIn('name', ['maintenance_mode', 'maintenance_content', 'maintenance_time'])->update(['value' => null]);
        }

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
    }

    // 注册验证码自动置无效 & 优惠券无效化
    private function expireCode(): void
    {
        // 注册验证码自动置无效
        VerifyCode::recentUnused()->update(['status' => 2]);

        // 优惠券到期 / 用尽的 自动置无效
        Coupon::withTrashed()->whereStatus(0)->where('end_time', '<=', time())->orWhereIn('type', [1, 2])->whereUsableTimes(0)->update(['status' => 2]);

        // 邀请码到期自动置无效
        Invite::whereStatus(0)->where('dateline', '<=', date('Y-m-d H:i:s'))->update(['status' => 2]);
    }

    // 封禁访问异常的订阅链接
    private function blockSubscribe(): void
    {
        if (sysConfig('is_subscribe_ban')) {
            $subscribe_ban_times = sysConfig('subscribe_ban_times');
            foreach (User::activeUser()->with('subscribe')->get() as $user) {
                if (! $user->subscribe || $user->subscribe->status === 0) { // 无订阅链接 或 已封
                    continue;
                }
                // 24小时内不同IP的请求次数
                $request_times = $user->subscribeLogs()
                    ->where('request_time', '>=', date('Y-m-d H:i:s', strtotime('-1 days')))
                    ->distinct()
                    ->count('request_ip');
                if ($request_times >= $subscribe_ban_times) {
                    $user->subscribe->update([
                        'status'   => 0,
                        'ban_time' => strtotime('+'.sysConfig('traffic_ban_time').' minutes'),
                        'ban_desc' => '存在异常，自动封禁',
                    ]);

                    // 记录封禁日志
                    Helpers::addUserBanLog($user->id, 0, '【完全封禁订阅】-订阅24小时内请求异常');
                }
            }
        }
    }

    // 封禁账号
    private function blockUsers(): void
    {
        // 禁用流量超限用户
        foreach (User::activeUser()->whereRaw('u + d >= transfer_enable')->get() as $user) {
            $user->update(['enable' => 0]);

            // 写入日志
            Helpers::addUserBanLog($user->id, 0, '【封禁代理】-流量已用完');
        }

        // 封禁1小时内流量异常账号
        if (sysConfig('is_traffic_ban')) {
            $trafficBanTime = sysConfig('traffic_ban_time');
            foreach (User::activeUser()->whereBanTime(null)->get() as $user) {
                // 多往前取5分钟，防止数据统计任务执行时间过长导致没有数据
                if ($user->isTrafficWarning()) {
                    $user->update([
                        'enable'   => 0,
                        'ban_time' => strtotime('+'.$trafficBanTime.' minutes'),
                    ]);

                    // 写入日志
                    Helpers::addUserBanLog($user->id, $trafficBanTime, '【临时封禁代理】-1小时内流量异常');
                }
            }
        }
    }

    // 解封被临时封禁的账号
    private function unblockUsers(): void
    {
        // 解封被临时封禁的账号
        $userList = User::whereEnable(0)->where('status', '>=', 0)->whereNotNull('ban_time')->where('ban_time', '<', time())->get();
        foreach ($userList as $user) {
            $user->update(['enable' => 1, 'ban_time' => null]);

            // 写入操作日志
            Helpers::addUserBanLog($user->id, 0, '【自动解封】-临时封禁到期');
        }

        // 可用流量大于已用流量也解封（比如：邀请返利自动加了流量）
        $userList = User::whereEnable(0)
            ->where('status', '>=', 0)
            ->whereBanTime(null)
            ->where('expired_at', '>=', date('Y-m-d'))
            ->whereRaw('u + d < transfer_enable')
            ->get();
        foreach ($userList as $user) {
            $user->update(['enable' => 1]);

            // 写入操作日志
            Helpers::addUserBanLog($user->id, 0, '【自动解封】-有流量解封');
        }
    }

    // 端口回收与分配
    private function dispatchPort(): void
    {
        // 自动分配端口
        User::activeUser()->wherePort(0)->get()->each(function ($user) {
            $user->update(['port' => Helpers::getPort()]);
        });

        // 被封禁 / 过期一个月 的账号自动释放端口
        User::where('port', '<>', 0)
            ->whereStatus(-1)
            ->orWhere('expired_at', '<=', date('Y-m-d', strtotime('-1 months')))
            ->update(['port' => 0]);
    }

    // 检测节点是否离线
    private function checkNodeStatus(): void
    {
        if (sysConfig('is_node_offline')) {
            $offlineCheckTimes = sysConfig('offline_check_times');
            $onlineNode = NodeHeartbeat::recently()->distinct()->pluck('node_id')->toArray();
            foreach (Node::whereIsRelay(0)->whereStatus(1)->get() as $node) {
                // 10分钟内无节点负载信息则认为是后端炸了
                $nodeTTL = ! in_array($node->id, $onlineNode, true);
                if ($nodeTTL && $offlineCheckTimes) {
                    // 已通知次数
                    $cacheKey = 'offline_check_times'.$node->id;
                    if (Cache::has($cacheKey)) {
                        $times = Cache::get($cacheKey);
                    } else {
                        // 键将保留24小时
                        Cache::put($cacheKey, 1, Day);
                        $times = 1;
                    }

                    if ($times < $offlineCheckTimes) {
                        Cache::increment($cacheKey);
                        PushNotification::send('节点异常警告', "节点**{$node->name}【{$node->ip}】**异常：**心跳异常，可能离线了**");
                    }
                }
            }
        }
    }
}
