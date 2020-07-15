<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Mail\userExpireWarning;
use App\Mail\userExpireWarningToday;
use App\Models\User;
use Illuminate\Console\Command;
use Log;
use Mail;

class UserExpireAutoWarning extends Command {
	protected static $systemConfig;
	protected $signature = 'userExpireAutoWarning';
	protected $description = '用户临近到期自动发邮件提醒';

	public function __construct() {
		parent::__construct();
		self::$systemConfig = Helpers::systemConfig();
	}

	public function handle(): void {
		$jobStartTime = microtime(true);

		// 用户临近到期自动发邮件提醒
		if(self::$systemConfig['expire_warning']){
			$this->userExpireWarning();
		}

		$jobEndTime = microtime(true);
		$jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

		Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
	}

	private function userExpireWarning(): void {
		// 只取SSR没被禁用的用户，其他不用管
		$userList = User::query()->whereEnable(1)->get();
		foreach($userList as $user){
			// 用户名不是邮箱的跳过
			if(false === filter_var($user->email, FILTER_VALIDATE_EMAIL)){
				continue;
			}

			// 计算剩余可用时间
			$lastCanUseDays = ceil(round(strtotime($user->expire_time) - strtotime(date('Y-m-d H:i:s'))) / Day);
			if($lastCanUseDays == 0){
				$title = '账号过期提醒';
				$content = '您的账号将于今天晚上【24:00】过期。';

				$logId = Helpers::addNotificationLog($title, $content, 1, $user->email);
				Mail::to($user->email)->send(new userExpireWarningToday($logId));
			}elseif($lastCanUseDays > 0 && $lastCanUseDays <= self::$systemConfig['expire_days']){
				$title = '账号过期提醒';
				$content = '您的账号还剩'.$lastCanUseDays.'天即将过期。';

				$logId = Helpers::addNotificationLog($title, $content, 1, $user->email);
				Mail::to($user->email)->send(new userExpireWarning($logId, $lastCanUseDays));
			}
		}
	}
}
