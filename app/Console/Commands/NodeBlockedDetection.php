<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Components\ServerChan;
use App\Http\Models\SsNode;
use App\Mail\nodeCrashWarning;
use Cache;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Log;
use Mail;

class NodeBlockedDetection extends Command
{
	protected $signature = 'NodeBlockedDetection';
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
				Log::info('下次节点TCP阻断检测时间：'.date('Y-m-d H:i:s', Cache::get('LastCheckTime')));
			}
		}

		$jobEndTime = microtime(TRUE);
		$jobUsedTime = round(($jobEndTime-$jobStartTime), 4);

		Log::info("执行定时任务【{$this->description}】，耗时 {$jobUsedTime} 秒");
	}

	// 监测节点状态
	private function checkNodes()
	{
		$title = "节点异常警告";
		$nodeList = SsNode::query()->where('is_transit', 0)->where('status', 1)->where('detectionType', '>', 0)->get();
		foreach($nodeList as $node){
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
			$text = '| 协议 | 状态 |'.PHP_EOL.'| ------ | ------ |'.PHP_EOL;
			$sendText = FALSE;
			if($node->detectionType == 1 || $node->detectionType == 3){
				$tcpCheck = $this->tcpCheck($node->ip, $node->single? $node->port : NULL);
				if($tcpCheck != FALSE){
					$text .= '| TCP |';
					switch($tcpCheck){
						case 1:
							$text .= ' 海外阻断 |'.PHP_EOL;
							break;
						case 2:
							$text .= ' 国内阻断 |'.PHP_EOL;
							break;
						case 3:
							$text .= ' 机器宕机 |'.PHP_EOL;
							break;
						case 0:
							$text .= ' 检测正常 |'.PHP_EOL;
							break;
						default:
							$text .= ' 未知 |'.PHP_EOL;
					}
					if($tcpCheck > 0){
						$sendText = TRUE;
					}
				}
			}
			if($node->detectionType == 2 || $node->detectionType == 3){
				$icmpCheck = $this->icmpCheck($node->ip);
				if($icmpCheck != FALSE){
					$text .= '| ICMP |';
					switch($icmpCheck){
						case 1:
							$text .= ' 海外阻断 |'.PHP_EOL;
							break;
						case 2:
							$text .= ' 国内阻断 |'.PHP_EOL;
							break;
						case 3:
							$text .= ' 机器宕机 |'.PHP_EOL;
							break;
						case 0:
							$text .= ' 检测正常 |'.PHP_EOL;
							break;
						default:
							$text .= ' 未知 |'.PHP_EOL;
					}
					if($icmpCheck > 0){
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

						$this->notifyMaster($title, "**{$node->name} - 【{$node->ip}】**：".PHP_EOL.$text, $node->name, $node->server);
					}elseif($times >= self::$systemConfig['numberOfWarningTimes']){
						Cache::forget($cacheKey);
						SsNode::query()->where('id', $node->id)->update(['status' => 0]);

						$this->notifyMaster($title, "**{$node->name} - 【{$node->ip}】**：".PHP_EOL.$text."节点自动进入维护状态".PHP_EOL, $node->name, $node->server);
					}
				}else{
					$this->notifyMaster($title, "**{$node->name} - 【{$node->ip}】**：".PHP_EOL.$text, $node->name, $node->server);
				}
				Log::info("【节点阻断检测】{$node->name} - 【{$node->ip}】: ".PHP_EOL.$text);
			}
		}

		// 随机生成下次检测时间
		$nextCheckTime = time()+3600;
		Cache::put('LastCheckTime', $nextCheckTime, 60);
	}

	/**
	 * 用api.50network.com进行节点阻断检测
	 *
	 * @param string $ip   被检测的IP
	 * @param int    $port 检测端口
	 *
	 * @return bool|int
	 */
	private function tcpCheck($ip, $port)
	{
		try{
			if(isset($port)){
				$url = 'https://api.50network.com/china-firewall/check/ip/tcp_port/'.$ip.'/'.$port;
			}else{
				$url = 'https://api.50network.com/china-firewall/check/ip/tcp_ack/'.$ip;
			}
			$ret = json_decode($this->curlRequest($url), TRUE);
			if(!$ret){
				Log::warning("【TCP阻断检测】检测".$ip."时，接口返回异常访问链接：");

				return FALSE;
			}elseif(!$ret['success']){
				Log::warning("【TCP阻断检测】检测".$ip."时，返回".$ret->error);

				return FALSE;
			}
		} catch(Exception $e){
			Log::warning("【TCP阻断检测】检测".$ip."时，接口请求超时");

			return FALSE;
		}

		if($ret['firewall-enable'] && $ret['firewall-disable']){
			return 0; // 正常
		}elseif($ret['firewall-enable'] && !$ret['firewall-disable']){
			return 1; // 国外访问异常
		}elseif(!$ret['firewall-enable'] && $ret['firewall-disable']){
			return 2; // 被墙
		}else{
			return 3; // 服务器宕机
		}
	}

	/**
	 * 用api.50network.com进行ICMP阻断检测
	 *
	 * @param string $ip 被检测的IP
	 *
	 * @return bool|int
	 */
	private function icmpCheck($ip)
	{
		try{
			$url = 'https://api.50network.com/china-firewall/check/ip/icmp/'.$ip;
			$ret = json_decode($this->curlRequest($url), TRUE);
			if(!$ret){
				Log::warning("【ICMP阻断检测】检测".$ip."时，接口返回异常访问链接：");

				return FALSE;
			}elseif(!$ret['success']){
				Log::warning("【ICMP阻断检测】检测".$ip."时，返回".$ret->error);

				return FALSE;
			}
		} catch(Exception $e){
			Log::warning("【ICMP阻断检测】检测".$ip."时，接口请求超时");

			return FALSE;
		}

		if($ret['firewall-enable'] && $ret['firewall-disable']){
			return 0; // 正常
		}elseif($ret['firewall-enable'] && !$ret['firewall-disable']){
			return 1; // 国外访问异常
		}elseif(!$ret['firewall-enable'] && $ret['firewall-disable']){
			return 2; // 被墙
		}else{
			return 3; // 服务器宕机
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
	 * @throws GuzzleException
	 */
	private function notifyMaster($title, $content, $nodeName, $nodeServer)
	{
		$this->notifyMasterByEmail($title, $content, $nodeName, $nodeServer);
		ServerChan::send($title, $content);
	}

	/**
	 * 发邮件通知管理员
	 *
	 * @param string $title      消息标题
	 * @param string $content    消息内容
	 * @param string $nodeName   节点名称
	 * @param string $nodeServer 节点域名
	 */
	private function notifyMasterByEmail($title, $content, $nodeName, $nodeServer)
	{
		if(self::$systemConfig['webmaster_email']){
			$logId = Helpers::addEmailLog(self::$systemConfig['webmaster_email'], $title, $content);
			Mail::to(self::$systemConfig['webmaster_email'])->send(new nodeCrashWarning($logId, $nodeName, $nodeServer));
		}
	}

	/**
	 * 发起一个CURL请求
	 *
	 * @param string $url  请求地址
	 * @param array  $data POST数据，留空则为GET
	 *
	 * @return mixed
	 */
	private function curlRequest($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_URL, $url);
		$result = curl_exec($ch);
		curl_close($ch);

		return $result;
	}
}
