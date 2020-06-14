<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Components\NetworkDetection;
use App\Components\PushNotification;
use App\Mail\nodeCrashWarning;
use App\Models\SsNode;
use Cache;
use Illuminate\Console\Command;
use Log;
use Mail;

class NodeBlockedDetection extends Command {
	protected static $systemConfig;
	protected $signature = 'nodeBlockedDetection';
	protected $description = '节点阻断检测';

	public function __construct() {
		parent::__construct();
		self::$systemConfig = Helpers::systemConfig();
	}

	public function handle() {
		$jobStartTime = microtime(true);
		if(self::$systemConfig['nodes_detection']){
			if(!Cache::has('LastCheckTime')){
				$this->checkNodes();
			}elseif(Cache::get('LastCheckTime') <= time()){
				$this->checkNodes();
			}else{
				Log::info('下次节点阻断检测时间：'.date('Y-m-d H:i:s', Cache::get('LastCheckTime')));
			}
		}

		$jobEndTime = microtime(true);
		$jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

		Log::info("---【{$this->description}】完成---，耗时 {$jobUsedTime} 秒");
	}

	// 监测节点状态
	private function checkNodes() {
		$nodeList = SsNode::query()->whereIsRelay(0)->whereStatus(1)->where('detection_type', '>', 0)->get();
		$sendText = false;
		$message = "| 线路 | 协议 | 状态 |\r\n| ------ | ------ | ------ |\r\n";
		$additionalMessage = '';
		foreach($nodeList as $node){
			$info = false;
			if($node->detection_type == 0){
				continue;
			}
			// 使用DDNS的node先通过gethostbyname获取ipv4地址
			if($node->is_ddns){
				$ip = gethostbyname($node->server);
				if(strcmp($ip, $node->server) != 0){
					$node->ip = $ip;
				}else{
					Log::warning("【节点阻断检测】检测".$node->server."时，IP获取失败".$ip." | ".$node->server);
					$this->notifyMaster("{$node->name}动态IP获取失败", "节点 {$node->name} ： IP获取失败 ");
				}
			}
			if($node->detection_type != 1){
				$icmpCheck = NetworkDetection::networkCheck($node->ip, true);
				if($icmpCheck != false && $icmpCheck != "通讯正常"){
					$message .= "| ".$node->name." | ICMP | ".$icmpCheck." |\r\n";
					$sendText = true;
					$info = true;
				}
			}
			if($node->detection_type != 2){
				$tcpCheck = NetworkDetection::networkCheck($node->ip, false, $node->single? $node->port : null);
				if($tcpCheck != false && $tcpCheck != "通讯正常"){
					$message .= "| ".$node->name." | TCP | ".$tcpCheck." |\r\n";
					$sendText = true;
					$info = true;
				}
			}

			// 节点检测次数
			if($info){
				if(self::$systemConfig['detection_check_times']){
					// 已通知次数
					$cacheKey = 'detection_check_times'.$node->id;
					if(Cache::has($cacheKey)){
						$times = Cache::get($cacheKey);
					}else{
						// 键将保留12小时，多10分钟防意外
						Cache::put($cacheKey, 1, 43800);
						$times = 1;
					}

					if($times < self::$systemConfig['detection_check_times']){
						Cache::increment($cacheKey);
					}else{
						Cache::forget($cacheKey);
						SsNode::query()->whereId($node->id)->update(['status' => 0]);
						$additionalMessage .= "\r\n节点【{$node->name}】自动进入维护状态\r\n";
					}
				}
			}
		}

		//只有在出现阻断线路时，才会发出警报
		if($sendText){
			$this->notifyMaster("节点阻断警告", "阻断日志: \r\n\r\n".$message.$additionalMessage);
			Log::info("阻断日志: \r\n".$message.$additionalMessage);
		}

		// 随机生成下次检测时间
		Cache::put('LastCheckTime', time() + mt_rand(3000, Hour), 3700);
	}

	/**
	 * 通知管理员
	 *
	 * @param  string  $title    消息标题
	 * @param  string  $content  消息内容
	 *
	 */
	private function notifyMaster($title, $content) {
		$result = PushNotification::send($title, $content);
		if(!$result && self::$systemConfig['webmaster_email']){
			$logId = Helpers::addNotificationLog($title, $content, 1, self::$systemConfig['webmaster_email']);
			Mail::to(self::$systemConfig['webmaster_email'])->send(new nodeCrashWarning($logId));
		}
	}
}
