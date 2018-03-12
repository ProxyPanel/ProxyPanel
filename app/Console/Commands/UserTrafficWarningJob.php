<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\Config;
use App\Http\Models\User;
use App\Http\Models\EmailLog;
use App\Mail\userTrafficWarning;
use Mail;
use Log;

class UserTrafficWarningJob extends Command
{
    protected $signature = 'userTrafficWarningJob';
    protected $description = '自动发邮件提醒用户流量超过警告阈值';

    protected static $config;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $config = $this->systemConfig();

        if ($config['traffic_warning']) {
            $userList = User::query()->where('transfer_enable', '>', 0)->whereIn('status', [0, 1])->where('enable', 1)->get();
            foreach ($userList as $user) {
                // 用户名不是邮箱的跳过
                if (false === filter_var($user->username, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }

                $usedPercent = round(($user->d + $user->u) / $user->transfer_enable, 2) * 100; // 已使用流量百分比
                if ($usedPercent >= $config['traffic_warning_percent']) {
                    $title = '流量警告';
                    $content = '流量已使用：' . $usedPercent . '%，超过设置的流量阈值' . $config['traffic_warning_percent'] . '%';

                    try {
                        Mail::to($user->username)->send(new userTrafficWarning($config['website_name'], $usedPercent));
                        $this->sendEmailLog($user->id, $title, $content);
                    } catch (\Exception $e) {
                        $this->sendEmailLog($user->id, $title, $content, 0, $e->getMessage());
                    }
                }
            }
        }

        Log::info('定时任务：' . $this->description);
    }

    /**
     * 写入邮件发送日志
     * @param int $user_id 用户ID
     * @param string $title 标题
     * @param string $content 内容
     * @param int $status 投递状态
     * @param string $error 投递失败时记录的异常信息
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

    // 系统配置
    private function systemConfig() {
        $config = Config::query()->get();
        $data = [];
        foreach ($config as $vo) {
            $data[$vo->name] = $vo->value;
        }

        return $data;
    }
}
