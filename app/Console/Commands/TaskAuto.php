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
    public function handle(): void
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

    private function orderTimer(): void
    {
        Order::where(function (Builder $query) {
            $query->recentUnPay(); // 关闭超时未支付本地订单
        })->orWhere(function (Builder $query) {
            $query->whereStatus(1)->where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-'.config('tasks.close.confirmation_orders').' hours'))); // 关闭未处理的人工支付订单
        })->chunk(config('tasks.chunk'), function ($orders) {
            $orders->each->close();
        });
    }

    private function expireCode(): void
    { // 注册验证码自动置无效 & 优惠券无效化
        // 注册验证码过时 置无效
        VerifyCode::recentUnused()->update(['status' => 2]);

        // 优惠券过时 置无效
        Coupon::withTrashed()->where('status', '<>', 2)->where('end_time', '<=', time())->update(['status' => 2]);

        // 邀请码过时 置无效
        Invite::whereStatus(0)->where('dateline', '<=', now())->update(['status' => 2]);
    }

    private function blockSubscribes(): void
    { // 封禁访问异常地订阅链接
        $trafficBanTime = sysConfig('traffic_ban_time');
        $ban_time = strtotime($trafficBanTime.' minutes');
        $dirtyWorks = ['status' => 0, 'ban_time' => $ban_time, 'ban_desc' => 'Subscription link receive abnormal access and banned by the system'];
        $banMsg = ['time' => $trafficBanTime, 'description' => __('[Auto Task] Blocked Subscription: Subscription with abnormal requests within 24 hours')];

        User::activeUser()->with(['subscribe', 'subscribeLogs'])->whereHas('subscribe', function (Builder $query) {
            $query->whereStatus(1); // 获取有订阅且未被封禁用户
        })->whereHas('subscribeLogs', function (Builder $query) {
            $query->whereDate('request_time', '>=', now()->subDay()); //    ->distinct()->count('request_ip');
        }, '>=', sysConfig('subscribe_ban_times'))->chunk(config('tasks.chunk'), function ($users) use ($banMsg, $dirtyWorks) {
            foreach ($users as $user) {
                $user->subscribe->update($dirtyWorks);
                $user->banedLogs()->create($banMsg); // 记录封禁日志
            }
        });
    }

    private function unblockSubscribes(): void
    {
        UserSubscribe::whereStatus(0)->where('ban_time', '<=', time())->chunk(config('tasks.chunk'), function ($subscribes) {
            $subscribes->each->update(['status' => 1, 'ban_time' => null, 'ban_desc' => null]);
        });
    }

    private function blockUsers(): void
    { // 封禁账号
        // 禁用流量超限用户
        User::activeUser()->whereRaw('u + d >= transfer_enable')->chunk(config('tasks.chunk'), function ($users) {
            $users->each(function ($user) {
                $user->update(['enable' => 0]);
                $user->banedLogs()->create(['description' => __('[Auto Task] Blocked service: Run out of traffic')]);
            });
        });

        // 封禁1小时内流量异常账号
        if (sysConfig('is_traffic_ban')) {
            $trafficBanTime = sysConfig('traffic_ban_time');
            $ban_time = strtotime($trafficBanTime.' minutes');

            User::activeUser()->whereBanTime(null)->where('t', '>=', strtotime('-5 minutes')) // 只检测最近5分钟有流量使用的用户
                ->chunk(config('tasks.chunk'), function ($users) use ($ban_time, $trafficBanTime) {
                    $users->each(function ($user) use ($ban_time, $trafficBanTime) {
                        if ($user->isTrafficWarning()) {
                            $user->update(['enable' => 0, 'ban_time' => $ban_time]);
                            $user->banedLogs()->create(['time' => $trafficBanTime, 'description' => __('[Auto Task] Blocked service: Abnormal traffic within 1 hour')]); // 写入日志
                        }
                    });
                });
        }
    }

    private function unblockUsers(): void
    { // 解封账号
        // 解封被临时封禁的账号
        User::bannedUser()->where('ban_time', '<', time())->chunk(config('tasks.chunk'), function ($users) {
            $users->each(function ($user) {
                $user->update(['enable' => 1, 'ban_time' => null]);
                $user->banedLogs()->create(['description' => __('[Auto Task] Unblocked Service: Account ban expired')]);
            });
        });

        // 可用流量大于已用流量也解封（比如：邀请返利加了流量）
        User::bannedUser()->whereBanTime(null)->where('expired_at', '>=', date('Y-m-d'))->whereRaw('u + d < transfer_enable')->chunk(config('tasks.chunk'), function ($users) {
            $users->each(function ($user) {
                $user->update(['enable' => 1]);
                $user->banedLogs()->create(['description' => __('[Auto Task] Unblocked Service: Account has available data traffic')]);
            });
        });
    }
}
