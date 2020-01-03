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
	protected $signature = 'nodeBlockedDetection';
	protected $description = '节点阻断检测';
	protected static $systemConfig;

	public function __construct()
	{
		parent::__construct();
		self::$systemConfig = Helpers::systemConfig();
	}

	public function handle()
	{
		$jobStartTime = microtime(TRUE);
		if(self::$systemConfig['nodes_detection']){
			if(!Cache::has('LastCheckTime')){
				$this->checkNodes();
			}elseif(Cache::get('LastCheckTime') <= time()){
				$this->checkNodes();
			}else{
				Log::info('下次节点阻断检测时间：'.date('Y-m-d H:i:s', Cache::get('LastCheckTime')));
			}
		}

		$jobEndTime = microtime(TRUE);
		$jobUsedTime = round(($jobEndTime-$jobStartTime), 4);

		Log::info("执行定时任务【{$this->description}】，耗时 {$jobUsedTime} 秒");
	}

	// 监测节点状态
	private function checkNodes()
	{
		$nodeList = SsNode::query()->where('is_transit', 0)->where('status', 1)->where('detectionType', '>', 0)->get();
		foreach($nodeList as $node){
			$title = "【{$node->name}】阻断警告";
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
					$this->notifyMaster($title, "节点**{$node->name}**：** IP获取失败 **", $node->name, $node->server);
				}
			}
			$sendText = FALSE;
			$text = "| 协议 | 状态 |\r\n| :------ | :------ |\r\n";
			if($node->detectionType != 1){
				$icmpCheck = $this->networkCheck($node->ip, TRUE, FALSE);
				if($icmpCheck != FALSE){
					$text .= "| ICMP | ".$icmpCheck."|\r\n";
					if($icmpCheck != '通讯正常'){
						$sendText = TRUE;
					}
				}
			}
			if($node->detectionType != 2){
				$tcpCheck = $this->networkCheck($node->ip, FALSE, $node->single? $node->port : FALSE);
				if($tcpCheck != FALSE){
					$text .= "| TCP | ".$tcpCheck."|\r\n";
					if($tcpCheck != '通讯正常'){
						$sendText = TRUE;
					}
				}
			}

			// 异常才发通知消息
			if($sendText){
				if(self::$systemConfig['numberOfWarningTimes']){
					// 已通知次数
					$cacheKey = 'numberOfWarningTimes'.$node->id;
					if(Cache::has($cacheKey)){
						$times = Cache::get($cacheKey);
					}else{
						Cache::put($cacheKey, 1, 725); // 最多设置提醒12次，12*60=720分钟缓存时效，多5分钟防止异常
						$times = 1;
					}

					if($times < self::$systemConfig['numberOfWarningTimes']){
						Cache::increment($cacheKey);
					}else{
						Cache::forget($cacheKey);
						SsNode::query()->where('id', $node->id)->update(['status' => 0]);
						$text .= "\r\n**节点自动进入维护状态**\r\n";
					}
				}
				$this->notifyMaster($title, "**{$node->name} - 【{$node->ip}】**: \r\n\r\n".$text, $node->name, $node->server);
				Log::info("【节点阻断检测】{$node->name} - 【{$node->ip}】: \r\n".$text);
			}
		}

		// 随机生成下次检测时间
		$nextCheckTime = time()+3600;
		Cache::put('LastCheckTime', $nextCheckTime, 60);
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
				Log::warning("【".$checkName."阻断检测】检测".$ip."时，返回".json_encode($ret));

				return FALSE;
			}
		} catch(Exception $e){
			Log::warning("【".$checkName."阻断检测】检测".$ip."时，接口请求超时".$e);

			return FALSE;
		}

		if($ret['firewall-enable'] && $ret['firewall-disable']){
			return '通讯正常'; // 正常
		}elseif($ret['firewall-enable'] && !$ret['firewall-disable']){
			return '海外阻断'; // 国外访问异常
		}elseif(!$ret['firewall-enable'] && $ret['firewall-disable']){
			return '国内阻断'; // 被墙
		}else{
			return '机器宕机'; // 服务器宕机
		}
	}

	/**
	 * 通知管理员
	 *
	 * @param string $title      消息标题
	 * @param string $content    消息内容
	 * @param string $nodeName   节点名称
	 * @param string $nodeServer 节点域名
	 *
	 */
	private function notifyMaster($title, $content, $nodeName, $nodeServer)
	{
		if(self::$systemConfig['webmaster_email']){
			$logId = Helpers::addEmailLog(self::$systemConfig['webmaster_email'], $title, $content);
			Mail::to(self::$systemConfig['webmaster_email'])->send(new nodeCrashWarning($logId, $nodeName, $nodeServer));
		}
		ServerChan::send($title, $content);
	}
}
