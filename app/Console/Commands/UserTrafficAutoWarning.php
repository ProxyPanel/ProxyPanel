<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use Illuminate\Console\Command;
use App\Http\Models\User;
use App\Http\Models\EmailLog;
use App\Mail\userTrafficWarning;
use Mail;
use Log;

class UserTrafficAutoWarning extends Command
{
    protected $signature = 'userTrafficAutoWarning';
    protected $description = '用户流量超过警告阈值自动发邮件提醒';
    protected static $systemConfig;

    public function __construct()
    {
        parent::__construct();
        self::$systemConfig = Helpers::systemConfig();
    }

    public function handle()
    {
        $jobStartTime = microtime(true);

        // 用户流量超过警告阈值自动发邮件提醒
        if (self::$systemConfig['traffic_warning']) {
            $this->userTrafficWarning();
        }

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('执行定时任务【' . $this->description . '】，耗时' . $jobUsedTime . '秒');
    }

    // 用户流量超过警告阈值自动发邮件提醒
    private function userTrafficWarning()
    {
        $userList = User::query()->where('status', '>=', 0)->where('enable', 1)->where('transfer_enable', '>', 0)->get();
        foreach ($userList as $user) {
            // 用户名不是邮箱的跳过
            if (false === filter_var($user->username, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $usedPercent = round(($user->d + $user->u) / $user->transfer_enable, 2) * 100; // 已使用流量百分比
            if ($usedPercent >= self::$systemConfig['traffic_warning_percent']) {
                $title = '流量提醒';
                $content = '流量已使用：' . $usedPercent . '%，请保持关注。';

                try {
                    Mail::to($user->username)->send(new userTrafficWarning(self::$systemConfig['website_name'], $usedPercent));
                    $this->sendEmailLog($user->id, $title, $content);
                } catch (\Exception $e) {
                    $this->sendEmailLog($user->id, $title, $content, 0, $e->getMessage());
                }
            }
        }
    }

    /**
     * 写入邮件发送日志
     *
     * @param int    $user_id 用户ID
     * @param string $title   标题
     * @param string $content 内容
     * @param int    $status  投递状态
     * @param string $error   投递失败时记录的异常信息
     */
    private function sendEmailLog($user_id, $title, $content, $status = 1, $error = '')
    {
        $emailLogObj = new EmailLog();
        $emailLogObj->user_id = $user_id;
        $emailLogObj->title = $title;
        $emailLogObj->content = $content;
        $emailLogObj->status = $status;
        $emailLogObj->error = $error;
        $emailLogObj->created_at = date('Y-m-d H:i:s');
        $emailLogObj->save();
    }
}
