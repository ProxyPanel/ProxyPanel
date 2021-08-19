<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Models\Config;
use App\Models\Coupon;
use App\Models\Invite;
use App\Models\Order;
use App\Models\User;
use App\Models\VerifyCode;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Log;

class AutoJob extends Command
{
    protected $signature = 'autoJob';
    protected $description = '自动化任务';

    /*
     * 警告：除非熟悉业务流程，否则不推荐更改以下执行顺序，随意变更以下顺序可能导致系统异常
     */
    public function handle()
    {
        $jobTime = microtime(true);

        Order::recentUnPay()->update(['status' => -1]); // 关闭超时未支付本地订单
        $this->expireCode(); //过期验证码、优惠券、邀请码无效化

        if (sysConfig('is_subscribe_ban')) {
            $this->blockSubscribe(); // 封禁访问异常的订阅链接
        }

        $this->blockUsers(); // 封禁账号
        $this->unblockUsers(); // 解封被封禁的账号

        if (sysConfig('auto_release_port')) {
            $this->dispatchPort(); // 端口回收与再分配
        }

        if (sysConfig('maintenance_mode') && sysConfig('maintenance_time') && sysConfig('maintenance_time') <= date('c')) {// 检查维护模式
            Config::whereIn('name', ['maintenance_mode', 'maintenance_content', 'maintenance_time'])->update(['value' => null]);
        }

        $jobTime = round((microtime(true) - $jobTime), 4);

        Log::info('---【'.$this->description.'】完成---，耗时'.$jobTime.'秒');
    }

    private function expireCode()// 注册验证码自动置无效 & 优惠券无效化
    {
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

    private function blockSubscribe()// 封禁访问异常的订阅链接
    {
        $subscribe_ban_times = sysConfig('subscribe_ban_times');
        User::activeUser()
            ->with(['subscribe', 'subscribeLogs'])
            ->whereHas('subscribe', function (Builder $query) {
                $query->whereStatus(0); // 获取有订阅且未被封禁用户
            })
            ->chunk(config('tasks.chunk'), function ($users) use ($subscribe_ban_times) {
                foreach ($users as $user) {
                    // 24小时内不同IP的请求次数
                    $request_times = $user->subscribeLogs()
                        ->where('request_time', '>=', date('Y-m-d H:i:s', strtotime('-1 days')))
                        ->distinct()
                        ->count('request_ip');
                    if ($request_times >= $subscribe_ban_times) {
                        $user->subscribe->update([
                            'status'   => 0,
                            'ban_time' => strtotime(sysConfig('traffic_ban_time').' minutes'),
                            'ban_desc' => '存在异常，自动封禁',
                        ]);
                        $user->banedLogs()->create(['description' => '【完全封禁订阅】-订阅24小时内请求异常']); // 记录封禁日志
                    }
                }
            });
    }

    private function blockUsers()// 封禁账号
    {
        // 禁用流量超限用户
        User::activeUser()
            ->whereRaw('u + d >= transfer_enable')
            ->chunk(config('tasks.chunk'), function ($users) {
                foreach ($users as $user) {
                    $user->update(['enable' => 0]);
                    $user->banedLogs()->create(['description' => '【封禁代理】-流量已用完']); // 写入日志
                }
            });

        // 封禁1小时内流量异常账号
        if (sysConfig('is_traffic_ban')) {
            $trafficBanTime = sysConfig('traffic_ban_time');
            User::activeUser()
                ->whereBanTime(null)
                ->chunk(config('tasks.chunk'), function ($users) use ($trafficBanTime) {
                    foreach ($users as $user) {
                        // 多往前取5分钟，防止数据统计任务执行时间过长导致没有数据
                        if ($user->isTrafficWarning()) {
                            $user->update([
                                'enable'   => 0,
                                'ban_time' => strtotime($trafficBanTime.' minutes'),
                            ]);
                            $user->banedLogs()->create(['time' => $trafficBanTime, 'description' => '【临时封禁代理】-1小时内流量异常']); // 写入日志
                        }
                    }
                });
        }
    }

    private function unblockUsers()// 解封被临时封禁的账号
    {
        // 解封被临时封禁的账号
        User::bannedUser()
            ->whereNotNull('ban_time')
            ->where('ban_time', '<', time())
            ->chunk(config('tasks.chunk'), function ($users) {
                foreach ($users as $user) {
                    $user->update(['enable' => 1, 'ban_time' => null]);
                    $user->banedLogs()->create(['description' => '【自动解封】-临时封禁到期']); // 写入操作日志
                }
            });

        // 可用流量大于已用流量也解封（比如：邀请返利自动加了流量）
        User::bannedUser()
            ->whereBanTime(null)
            ->where('expired_at', '>=', date('Y-m-d'))
            ->whereRaw('u + d < transfer_enable')
            ->chunk(config('tasks.chunk'), function ($users) {
                foreach ($users as $user) {
                    $user->update(['enable' => 1]);
                    $user->banedLogs()->create(['description' => '【自动解封】-有流量解封']); // 写入操作日志
                }
            });
    }

    // 端口回收与分配
    private function dispatchPort()
    {
        // 自动分配端口
        User::activeUser()
            ->wherePort(0)
            ->chunk(config('tasks.chunk'), function ($users) {
                foreach ($users as $user) {
                    $user->update(['port' => Helpers::getPort()]);
                }
            });

        // 被封禁 / 过期xx天 的账号自动释放端口
        User::where('port', '<>', 0)
            ->where(function ($query) {
                $query->whereStatus(-1)->orWhere('expired_at', '<=', date('Y-m-d', strtotime('-'.config('tasks.release_port').' days')));
            })
            ->update(['port' => 0]);
    }
}
