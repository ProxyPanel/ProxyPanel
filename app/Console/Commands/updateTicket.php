<?php

namespace App\Console\Commands;

use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Console\Command;
use Log;

class updateTicket extends Command {
	protected $signature = 'updateTicket';
	protected $description = '更新工单';

	public function __construct() {
		parent::__construct();
	}

	public function handle() {
		Log::info('----------------------------【更新工单】开始----------------------------');
		// 获取管理员
		$adminList = User::query()->whereIsAdmin(1)->get();
		foreach($adminList as $admin){
			Log::info('----------------------------【更新管理员'.$admin->id.'回复工单】开始----------------------------');
			// 获取该管理回复过的工单
			$replyList = TicketReply::query()->whereUserId($admin->id)->get();
			// 更新工单
			foreach($replyList as $reply){
				$ret = TicketReply::query()->whereId($reply->id)->update(['user_id' => 0, 'admin_id' => $admin->id]);
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