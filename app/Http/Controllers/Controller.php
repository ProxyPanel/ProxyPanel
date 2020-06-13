<?php

namespace App\Http\Controllers;

use App\Models\SensitiveWords;
use App\Models\SsNode;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController {
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	// 生成随机密码
	public function makePasswd() {
		return makeRandStr();
	}

	// 生成UUID
	public function makeUUID() {
		return createGuid();
	}

	// 生成网站安全码
	public function makeSecurityCode() {
		return strtolower(makeRandStr(8));
	}

	// 类似Linux中的tail命令
	public function tail($file, $n, $base = 5) {
		$fileLines = $this->countLine($file);
		if($fileLines < 15000){
			return false;
		}

		$fp = fopen($file, "r+");
		assert($n > 0);
		$pos = $n + 1;
		$lines = [];
		while(count($lines) <= $n){
			try{
				fseek($fp, -$pos, SEEK_END);
			}catch(Exception $e){
				break;
			}

			$pos *= $base;
			while(!feof($fp)){
				array_unshift($lines, fgets($fp));
			}
		}

		return array_slice($lines, 0, $n);
	}

	/**
	 * 计算文件行数
	 *
	 * @param $file
	 *
	 * @return int
	 */
	public function countLine($file) {
		$fp = fopen($file, "r");
		$i = 0;
		while(!feof($fp)){
			//每次读取2M
			if($data = fread($fp, 1024 * 1024 * 2)){
				//计算读取到的行数
				$num = substr_count($data, "\n");
				$i += $num;
			}
		}

		fclose($fp);

		return $i;
	}

	// 获取敏感词
	public function sensitiveWords($type) {
		return SensitiveWords::query()->whereType($type)->get()->pluck('words')->toArray();
	}

	// 将Base64图片转换为本地图片并保存
	function base64ImageSaver($base64_image_content) {
		// 匹配出图片的格式
		if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
			$type = $result[2];

			$directory = date('Ymd');
			$path = '/assets/images/qrcode/'.$directory.'/';
			if(!file_exists(public_path($path))){ // 检查是否有该文件夹，如果没有就创建，并给予最高权限
				mkdir(public_path($path), 0755, true);
			}

			$fileName = makeRandStr(18, true).".{$type}";
			if(file_put_contents(public_path($path.$fileName),
				base64_decode(str_replace($result[1], '', $base64_image_content)))){
				chmod(public_path($path.$fileName), 0744);

				return $path.$fileName;
			}else{
				return '';
			}
		}else{
			return '';
		}
	}

	/**
	 * 节点信息
	 *
	 * @param  int  $uid       用户ID
	 * @param  int  $nodeId    节点ID
	 * @param  int  $infoType  信息类型：0为链接，1为文字
	 *
	 * @return string
	 */
	function getNodeInfo($uid, $nodeId, $infoType) {
		$user = User::whereId($uid)->first();
		$node = SsNode::whereId($nodeId)->first();
		$scheme = null;
		// 获取分组名称
		$group = $node->getLevel()->first()->name;
		$host = $node->server?: $node->ip;
		$data = null;
		switch($node->type){
			case 1:
				if($node->single){
					$port = $node->port;
					$protocol = $node->protocol;
					$method = $node->method;
					$obfs = $node->obfs;
					$passwd = $node->passwd;
					$protocol_param = $user->port.':'.$user->passwd;
				}else{
					$port = $user->port;
					$protocol = $user->protocol;
					$method = $user->method;
					$obfs = $user->obfs;
					$passwd = $user->passwd;
					$protocol_param = $node->protocol_param;
				}

				if($infoType != 1){
					// 生成ss/ssr scheme
					if($node->compatible){
						$data = 'ss://'.base64url_encode($method.':'.$passwd.'@'.$host.':'.$port).'#'.$group;
					}else{
						$data = 'ssr://'.base64url_encode($host.':'.$port.':'.$protocol.':'.$method.':'.$obfs.':'.base64url_encode($passwd).'/?obfsparam='.base64url_encode($node->obfs_param).'&protoparam='.base64url_encode($protocol_param).'&remarks='.base64url_encode($node->name).'&group='.base64url_encode($group).'&udpport=0&uot=0');
					}
				}else{
					// 生成文本配置信息
					$data = "服务器：".$host.PHP_EOL."IPv6：".$node->ipv6.PHP_EOL."服务器端口：".$port.PHP_EOL."密码：".$passwd.PHP_EOL."加密：".$method.PHP_EOL.($node->compatible? '' : "协议：".$protocol.PHP_EOL."协议参数：".$protocol_param.PHP_EOL."混淆：".$obfs.PHP_EOL."混淆参数：".$node->obfs_param.PHP_EOL);
				}
				break;
			case 2:
				// 生成v2ray scheme
				if($infoType != 1){
					// 生成v2ray scheme
					$data = 'vmess://'.base64url_encode(json_encode([
							"v"    => "2",
							"ps"   => $node->name,
							"add"  => $host,
							"port" => $node->v2_port,
							"id"   => $user->uuid,
							"aid"  => $node->v2_alter_id,
							"net"  => $node->v2_net,
							"type" => $node->v2_type,
							"host" => $node->v2_host,
							"path" => $node->v2_path,
							"tls"  => $node->v2_tls? "tls" : ""
						], JSON_PRETTY_PRINT));
				}else{
					$data = "服务器：".$host.PHP_EOL."IPv6：".($node->ipv6?: "").PHP_EOL."端口：".$node->v2_port.PHP_EOL."加密方式：".$node->v2_method.PHP_EOL."用户ID：".$user->uuid.PHP_EOL."额外ID：".$node->v2_alter_id.PHP_EOL."传输协议：".$node->v2_net.PHP_EOL."伪装类型：".$node->v2_type.PHP_EOL."伪装域名：".($node->v2_host?: "").PHP_EOL."路径：".($node->v2_path?: "").PHP_EOL."TLS：".($node->v2_tls? "tls" : "").PHP_EOL;
				}
				break;
			case 3:
				break;
			default:
		}

		return $data;
	}
}
