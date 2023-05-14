<?php

namespace App\Console\Commands;

use App\Models\Config;
use App\Models\Coupon;
use App\Models\Invite;
use App\Models\Order;
use App\Models\User;
use App\Models\UserSubscribe;
use App\Models\VerifyCode;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Log;

class TaskAuto extends Command
{
    protected $signature = 'task:auto';

    protected $description = '自动任务';

    /*
     * 警告：除非熟悉业务流程，否则不推荐更改以下执行顺序，随意变更以下顺序可能导致系统异常
     */
    public function handle()
    {
        $jobTime = microtime(true);

        $this->orderTimer(); // 超时订单
        $this->expireCode(); // 过期验证码、优惠券、邀请码无效化
        if (sysConfig('is_subscribe_ban')) {
            $this->blockSubscribes(); // 封禁访问异常的订阅
        }
        $this->unblockSubscribes(); // 解禁订阅
        $this->blockUsers(); // 封禁账号
        $this->unblockUsers(); // 解封被封禁的账号

        if (sysConfig('maintenance_mode') && sysConfig('maintenance_time') && sysConfig('maintenance_time') <= date('c')) { // 检查维护模式
            Config::whereIn('name', ['maintenance_mode', 'maintenance_time'])->update(['value' => null]);
        }

        $jobTime = round(microtime(true) - $jobTime, 4);
        Log::info(__('----「:job」Completed, Used :time seconds ----', ['job' => $this->description, 'time' => $jobTime]));
    }

    private function orderTimer()
    {
        Order::recentUnPay()->chunk(config('tasks.chunk'), function ($orders) {
            $orders->each->close();
        }); // 关闭超时未支付本地订单

        Order::whereStatus(1)->where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-'.config('tasks.close.confirmation_orders').' hours')))->chunk(config('tasks.chunk'), function ($orders) {
            $orders->each->close();
        }); // 关闭未处理的人工支付订单
    }

    private function expireCode()
    { // 注册验证码自动置无效 & 优惠券无效化
        // 注册验证码自动置无效
        VerifyCode::recentUnused()->update(['status' => 2]);

        // 优惠券到期 / 用尽的 自动置无效
        Coupon::withTrashed()
            ->whereStatus(0)
            ->where('end_time', '<=', time())
            ->orWhereIn('type', [1, 2])
            ->whereUsableTimes(0)
            ->update(['status' => 2]);

        // 邀请码到期自动置无效
        Invite::whereStatus(0)
            ->where('dateline', '<=', date('Y-m-d H:i:s'))
            ->update(['status' => 2]);
    }

    private function blockSubscribes()
    { // 封禁访问异常的订阅链接
        User::activeUser()
            ->with(['subscribe', 'subscribeLogs'])
            ->whereHas('subscribe', function (Builder $query) {
                $query->whereStatus(1); // 获取有订阅且未被封禁用户
            })
            ->whereHas('subscribeLogs', function (Builder $query) {
                $query->where('request_time', '>=', date('Y-m-d H:i:s', strtotime('-1 day'))); //    ->distinct()->count('request_ip');
            }, '>=', sysConfig('subscribe_ban_times'))
            ->chunk(config('tasks.chunk'), function ($users) {
                $trafficBanTime = sysConfig('traffic_ban_time');
                $ban_time = strtotime($trafficBanTime.' minutes');
                $dirtyWorks = ['status' => 0, 'ban_time' => $ban_time, 'ban_desc' => 'Subscription link receive abnormal access and banned by the system'];
                $banMsg = ['time' => $trafficBanTime, 'description' => __('[Auto Job] Blocked Subscription: Subscription with abnormal requests within 24 hours')];
                foreach ($users as $user) {
                    $user->subscribe->update($dirtyWorks);
                    $user->banedLogs()->create($banMsg); // 记录封禁日志
                }
            });
    }

    private function unblockSubscribes()
    {
        UserSubscribe::whereStatus(0)->where('ban_time', '<=', time())->chunk(config('tasks.chunk'), function ($subscribes) {
            foreach ($subscribes as $subscribe) {
                $subscribe->update(['status' => 1, 'ban_time' => null, 'ban_desc' => null]);
            }
        });
    }

    private function blockUsers()
    { // 封禁账号
        // 禁用流量超限用户
        User::activeUser()
            ->whereRaw('u + d >= transfer_enable')
            ->chunk(config('tasks.chunk'), function ($users) {
                foreach ($users as $user) {
                    $user->update(['enable' => 0]);
                    $user->banedLogs()->create(['description' => __('[Auto Job] Blocked service: Run out of traffic')]); // 写入日志
                }
            });

        // 封禁1小时内流量异常账号
        if (sysConfig('is_traffic_ban')) {
            $trafficBanTime = sysConfig('traffic_ban_time');
            User::activeUser()
                ->whereBanTime(null)
                ->chunk(config('tasks.chunk'), function ($users) use ($trafficBanTime) {
                    $ban_time = strtotime($trafficBanTime.' minutes');
                    foreach ($users as $user) {
                        // 多往前取5分钟，防止数据统计任务执行时间过长导致没有数据
                        if ($user->isTrafficWarning()) {
                            $user->update(['enable' => 0, 'ban_time' => $ban_time]);
                            $user->banedLogs()->create(['time' => $trafficBanTime, 'description' => __('[Auto Job] Blocked service: Abnormal traffic within 1 hour')]); // 写入日志
                        }
                    }
                });
        }
    }

    private function unblockUsers()
    { // 解封账号
        // 解封被临时封禁的账号
        User::bannedUser()
            ->where('ban_time', '<', time())
            ->chunk(config('tasks.chunk'), function ($users) {
                foreach ($users as $user) {
                    $user->update(['enable' => 1, 'ban_time' => null]);
                    $user->banedLogs()->create(['description' => '[自动任务]解封服务: 封禁到期']); // 写入操作日志
                }
            });

        // 可用流量大于已用流量也解封（比如：邀请返利加了流量）
        User::bannedUser()
            ->whereBanTime(null)
            ->where('expired_at', '>=', date('Y-m-d'))
            ->whereRaw('u + d < transfer_enable')
            ->chunk(config('tasks.chunk'), function ($users) {
                foreach ($users as $user) {
                    $user->update(['enable' => 1]);
                    $user->banedLogs()->create(['description' => '[自动任务]解封服务: 出现可用流量']); // 写入操作日志
                }
            });
    }
}
