<?php

namespace App\Console\Commands;

use App\Models\SsNode;
use App\Models\SsNodeTrafficDaily;
use App\Models\User;
use App\Models\UserTrafficDaily;
use App\Models\UserTrafficLog;
use Illuminate\Console\Command;
use Log;

class fixDailyTrafficLogError extends Command {
	protected $signature = 'fixDailyTrafficLogError';
	protected $description = '修复原版本的每日流量计算错误';

	public function handle(): void {
		$end = date('Y-m-d 23:59:59', strtotime("-1 days"));

		Log::info('----------------------------【修复原版本的每日流量计算错误】开始----------------------------');
		Log::info('----------------------------【节点流量日志修正】开始----------------------------');
		foreach(SsNodeTrafficDaily::all() as $log){
			SsNodeTrafficDaily::query()->whereId($log->id)->update([
				'created_at' => date('Y-m-d H:i:s', strtotime("-1 days", strtotime($log->created_at)))
			]);
		}

		foreach(SsNode::all() as $node){
			$query = UserTrafficLog::query()
			                       ->whereNodeId($node->id)
			                       ->whereBetween('log_time',
				                       [strtotime(date('Y-m-d', strtotime("-1 days"))), strtotime($end)]);

			$u = $query->sum('u');
			$d = $query->sum('d');
			$total = $u + $d;

			if($total){ // 有数据才记录
				$obj = new SsNodeTrafficDaily();
				$obj->node_id = $node->id;
				$obj->u = $u;
				$obj->d = $d;
				$obj->total = $total;
				$obj->traffic = flowAutoShow($total);
				$obj->created_at = $end;
				$obj->save();
			}
		}
		Log::info('----------------------------【节点流量日志修正】结束----------------------------');

		Log::info('----------------------------【用户流量日志修正】开始----------------------------');
		foreach(UserTrafficDaily::all() as $log){
			UserTrafficDaily::query()->whereId($log->id)->update([
				'created_at' => date('Y-m-d H:i:s', strtotime("-1 days", strtotime($log->created_at)))
			]);
		}
		Log::info('----------------------------【用户个人流量日志修正】开始----------------------------');
		foreach(User::query()->whereIn('id',UserTrafficLog::query()->distinct()->pluck('user_id')->toArray())->get() as $user){
			// 统计一次所有节点的总和
			$this->statisticsByUser($user->id);

			// 统计每个节点产生的流量
			foreach(SsNode::query()->whereStatus(1)->orderBy('id')->get() as $node){
				$this->statisticsByUser($user->id, $node->id);
			}
		}
		Log::info('----------------------------【用户个人流量日志修正】结束----------------------------');
		Log::info('----------------------------【用户流量日志修正】结束----------------------------');
		Log::info('----------------------------【修复原版本的每日流量计算错误】结束----------------------------');
	}

	private function statisticsByUser($user_id, $node_id = 0): void {
		$end = date('Y-m-d 23:59:59', strtotime("-1 days"));

		$query = UserTrafficLog::query()
		                       ->whereUserId($user_id)
		                       ->whereBetween('log_time',
			                       [strtotime(date('Y-m-d', strtotime("-1 days"))), strtotime($end)]);

		if($node_id){
			$query->whereNodeId($node_id);
		}

		$u = $query->sum('u');
		$d = $query->sum('d');
		$total = $u + $d;

		if($total){ // 有数据才记录
			$obj = new UserTrafficDaily();
			$obj->user_id = $user_id;
			$obj->node_id = $node_id;
			$obj->u = $u;
			$obj->d = $d;
			$obj->total = $total;
			$obj->traffic = flowAutoShow($total);
			$obj->created_at = $end;
			$obj->save();
		}
	}

}