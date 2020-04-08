<?php

namespace App\Console\Commands;

use App\Http\Models\User;
use Illuminate\Console\Command;
use Log;

class upgradeUserResetTime extends Command
{
	protected $signature = 'upgradeUserResetTime';
	protected $description = '升级用户重置日期';

	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
		Log::info('----------------------------【升级用户重置日期】开始----------------------------');

		$userList = User::query()->get();
		foreach($userList as $user){
			$reset_time = NULL;
			if($user->traffic_reset_day){
				$today = date('d');// 今天 日期
				$last_day = date('t'); //本月最后一天
				$next_last_day = date('t', strtotime("+1 month"));//下个月最后一天
				$resetDay = $user->traffic_reset_day;// 用户原本的重置日期
				// 案例：31 29，重置日 大于 本月最后一天
				if($resetDay > $last_day){
					//往后推一个月
					$resetDay = $resetDay-$last_day;
					$reset_time = date('Y-m-'.$resetDay, strtotime("+1 month"));
					//案例：20<30<31
				}elseif($resetDay < $last_day && $resetDay > $today){
					$reset_time = date('Y-m-'.$resetDay);
					// 本日为重置日
				}elseif($resetDay == $today){
					$reset_time = date('Y-m-d', strtotime("+1 month"));
					//本月已经重置过了
				}elseif($resetDay < $today){
					//类似第一种情况，向后推一月
					if($resetDay > $next_last_day){
						$resetDay = $resetDay-$next_last_day;
						$reset_time = date('Y-m-'.$resetDay, strtotime("+1 month"));
					}else{
						$reset_time = date('Y-m-'.$resetDay, strtotime("+1 month"));
					}
				}
				// 用户账号有效期大于重置日期
				if($reset_time > $user->expire_time){
					$reset_time = NULL;
				}
				User::query()->where('id', $user->id)->update(['reset_time' => $reset_time]);
			}

			Log::info('---用户[ID：'.$user->id.' - '.$user->username.' ('.$user->email.')]的新重置日期为'.($reset_time != NULL? '【'.$reset_time.'】' : '【无】').'---');
		}

		Log::info('----------------------------【升级用户重置日期】结束----------------------------');
	}
}
