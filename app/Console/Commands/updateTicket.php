<?php

namespace App\Console\Commands;

use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Console\Command;
use Log;

class updateTicket extends Command {
	protected $signature = 'updateTicket';
	protected $description = '更新工单';

	public function handle(): void {
		Log::info('----------------------------【更新工单】开始----------------------------');
		// 获取管理员
		foreach(User::whereIsAdmin(1)->get() as $admin){
			Log::info('----------------------------【更新管理员'.$admin->id.'回复工单】开始----------------------------');
			// 获取该管理回复过的工单, 更新工单
			foreach(TicketReply::whereUserId($admin->id)->get() as $reply){
				$ret = TicketReply::whereId($reply->id)->update(['user_id' => 0, 'admin_id' => $admin->id]);
				if($ret){
					Log::info('--- 管理员：'.$admin->email.'回复子单ID：'.$reply->id.' ---');
				}else{
					Log::error('更新回复子单ID：【'.$reply->id.'】 失败！');
				}
			}
			Log::info('----------------------------【更新管理员'.$admin->id.'回复工单】完成----------------------------');
		}
		Log::info('----------------------------【更新工单】结束----------------------------');
	}
}