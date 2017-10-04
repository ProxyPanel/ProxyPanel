<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\Config;
use App\Http\Models\User;
use App\Mail\userExpireWarning;
use Mail;
use Log;

class UserExpireWarningJob extends Command
{
    protected $signature = 'command:userExpireWarningJob';
    protected $description = '用户到期提醒发邮件';

    protected static $config;

    public function __construct()
    {
        parent::__construct();

        $config = Config::get();
        $data = [];
        foreach ($config as $vo) {
            $data[$vo->name] = $vo->value;
        }

        self::$config = $data;
    }

    public function handle()
    {
        if (self::$config['expire_warning']) {
            $userList = User::where('transfer_enable', '>', 0)->whereIn('status', [0, 1])->where('enable', 1)->get();
            foreach ($userList as $user) {
                // 用户名不是邮箱的跳过
                if (false === filter_var($user->username, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }

                $lastCanUseDays = floor(round(strtotime($user->expire_time) - strtotime(date('Y-m-d H:i:s'))) / 3600 / 24);
                if ($lastCanUseDays > 0 && $lastCanUseDays <= self::$config['expire_days']) {
                    $title = '账号过期提醒';
                    $content = '账号还剩【' . $lastCanUseDays . '】天即将过期';

                    try {
                        Mail::to($user->username)->send(new userExpireWarning(self::$config['website_name'], $lastCanUseDays));
                        $this->sendEmailLog($user->id, $title, $content);
                    } catch (\Exception $e) {
                        $this->sendEmailLog($user->id, $title, $content, 0, $e->getMessage());
                    }
                }
            }
        }

        Log::info('定时任务：' . $this->description);
    }
}
