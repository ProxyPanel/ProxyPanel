<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Http\Models\User;
use App\Mail\userTrafficWarning;
use Illuminate\Console\Command;
use Log;
use Mail;

class UserTrafficAutoWarning extends Command {
	protected static $systemConfig;
	protected $signature = 'userTrafficAutoWarning';
	protected $description = '用户流量超过警告阈值自动发邮件提醒';

	public function __construct() {
		parent::__construct();
		self::$systemConfig = Helpers::systemConfig();
	}

	public function handle() {
		$jobStartTime = microtime(true);

		// 用户流量超过警告阈值自动发邮件提醒
		if(self::$systemConfig['traffic_warning']){
			$this->userTrafficWarning();
		}

		$jobEndTime = microtime(true);
		$jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

		Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
	}

	// 用户流量超过警告阈值自动发邮件提醒
	private function userTrafficWarning() {
		$userList = User::query()->where('status', '>=', 0)->whereEnable(1)->where('transfer_enable', '>', 0)->get();
		foreach($userList as $user){
			// 用户名不是邮箱的跳过
			if(false === filter_var($user->email, FILTER_VALIDATE_EMAIL)){
				continue;
			}

			$usedPercent = round(($user->d + $user->u) / $user->transfer_enable, 2) * 100; // 已使用流量百分比
			if($usedPercent >= self::$systemConfig['traffic_warning_percent']){
				$title = '流量提醒';
				$content = '流量已使用：'.$usedPercent.'%，请保持关注。';

				$logId = Helpers::addNotificationLog($title, $content, 1, $user->email);
				Mail::to($user->email)->send(new userTrafficWarning($logId, $usedPercent));
			}
		}
	}
}
