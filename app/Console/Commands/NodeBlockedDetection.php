<?php

namespace App\Console\Commands;

use App\Components\Curl;
use App\Components\Helpers;
use App\Components\ServerChan;
use App\Http\Models\SsNode;
use App\Mail\nodeCrashWarning;
use Cache;
use Exception;
use Illuminate\Console\Command;
use Log;
use Mail;

class NodeBlockedDetection extends Command
{
	protected static $systemConfig;
	protected $signature = 'nodeBlockedDetection';
	protected $description = '节点阻断检测';

	public function __construct()
	{
		parent::__construct();
		self::$systemConfig = Helpers::systemConfig();
	}

	public function handle()
	{
		$jobStartTime = microtime(TRUE);
		if(self::$systemConfig['nodes_detection']){
			$this->checkNodes();
		}

		$jobEndTime = microtime(TRUE);
		$jobUsedTime = round(($jobEndTime-$jobStartTime), 4);

		Log::info("---【{$this->description}】完成---，耗时 {$jobUsedTime} 秒");
	}

	// 监测节点状态
	private function checkNodes()
	{
		$nodeList = SsNode::query()->where('is_transit', 0)->where('status', 1)->where('detectionType', '>', 0)->get();
		$sendText = FALSE;
		$message = "| 线路 | 协议 | 状态 |\r\n| ------ | ------ | ------ |\r\n";
		$additionalMessage = '';
		foreach($nodeList as $node){
			$info = FALSE;
			if($node->detectionType == 0){
				continue;
			}
			// 使用DDNS的node先通过gethostbyname获取ipv4地址
			if($node->is_ddns){
				$ip = gethostbyname($node->server);
				if(strcmp($ip, $node->server) != 0){
					$node->ip = $ip;
				}else{
					Log::warning("【节点阻断检测】检测".$node->server."时，IP获取失败".$ip." | ".$node->server);
					$this->notifyMaster("{$node->name}动态IP获取失败", "节点**{$node->name}**：** IP获取失败 **");
				}
			}
			if($node->detectionType != 1){
				$icmpCheck = $this->networkCheck($node->ip, TRUE, FALSE);
				if($icmpCheck != FALSE && $icmpCheck != "通讯正常"){
					$message .= "| ".$node->name." | ICMP | ".$icmpCheck." |\r\n";
					$sendText = TRUE;
					$info = TRUE;
				}
			}

			if($node->detectionType != 2){
				$tcpCheck = $this->networkCheck($node->ip, FALSE, $node->single? $node->port : FALSE);
				if($tcpCheck != FALSE && $tcpCheck != "通讯正常"){
					$message .= "| ".$node->name." | TCP | ".$tcpCheck." |\r\n";
					$sendText = TRUE;
					$info = TRUE;
				}
			}

			// 节点检测次数
			if($info){
				if(self::$systemConfig['numberOfWarningTimes']){
					// 已通知次数
					$cacheKey = 'numberOfWarningTimes'.$node->id;
					if(Cache::has($cacheKey)){
						$times = Cache::get($cacheKey);
					}else{
						// 键将保留12小时，多10分钟防意外
						Cache::put($cacheKey, 1, 83800);
						$times = 1;
					}

					if($times < self::$systemConfig['numberOfWarningTimes']){
						Cache::increment($cacheKey);
					}else{
						Cache::forget($cacheKey);
						SsNode::query()->where('id', $node->id)->update(['status' => 0]);
						$additionalMessage .= "\r\n**节点【{$node->name}】自动进入维护状态**\r\n";
					}
				}
			}
		}

		//只有在出现阻断线路时，才会发出警报
		if($sendText){
			$this->notifyMaster("节点阻断警告", "**阻断日志**: \r\n\r\n".$message.$additionalMessage);
			Log::info("阻断日志: \r\n".$message.$additionalMessage);
		}
	}

	/**
	 * 通知管理员
	 *
	 * @param string $title   消息标题
	 * @param string $content 消息内容
	 *
	 */
	private function notifyMaster($title, $content)
	{
		if(self::$systemConfig['webmaster_email']){
			$logId = Helpers::addEmailLog(self::$systemConfig['webmaster_email'], $title, $content);
			Mail::to(self::$systemConfig['webmaster_email'])->send(new nodeCrashWarning($logId));
		}
		ServerChan::send($title, $content);
	}

	/**
	 * 用api.50network.com进行节点阻断检测
	 *
	 * @param string  $ip   被检测的IP
	 * @param boolean $type true 为ICMP,false 为tcp
	 * @param int     $port 检测端口
	 *
	 * @return bool|string
	 */
	private function networkCheck($ip, $type, $port)
	{
		$url = 'https://api.50network.com/china-firewall/check/ip/'.($type? 'icmp/' : ($port? 'tcp_port/' : 'tcp_ack/')).$ip.($port? '/'.$port : '');
		$checkName = $type? 'ICMP' : 'TCP';

		try{
			$ret = json_decode(Curl::send($url), TRUE);
			if(!$ret){
				Log::warning("【".$checkName."阻断检测】检测".$ip."时，接口返回异常访问链接：".$url);

				return FALSE;
			}elseif(!$ret['success']){
				if($ret['error'] == "execute timeout (3s)"){
					sleep(10);

					return $this->networkCheck($ip, $type, $port);
				}else{
					Log::warning("【".$checkName."阻断检测】检测".$ip.($port? : '')."时，返回".json_encode($ret));

				}


				return FALSE;
			}
		} catch(Exception $e){
			Log::warning("【".$checkName."阻断检测】检测".$ip."时，接口请求超时".$e);

			return FALSE;
		}

		if($ret['firewall-enable'] && $ret['firewall-disable']){
			return "通讯正常"; // 正常
		}elseif($ret['firewall-enable'] && !$ret['firewall-disable']){
			return "海外阻断"; // 国外访问异常
		}elseif(!$ret['firewall-enable'] && $ret['firewall-disable']){
			return "国内阻断"; // 被墙
		}else{
			return "机器宕机"; // 服务器宕机
		}
	}
}
