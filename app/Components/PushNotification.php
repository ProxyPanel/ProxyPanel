<?php


namespace App\Components;

use Exception;
use Log;
use stdClass;

class PushNotification {
	public static function send($title, $content) {
		switch(Helpers::systemConfig()['is_notification']){
			case 'serverChan':
				return self::ServerChan($title, $content);
				break;
			case 'bark':
				return self::Bark($title, $content);
				break;
			default:
				return false;
		}
	}

	/**
	 * ServerChan推送消息
	 *
	 * @param  string  $title    消息标题
	 * @param  string  $content  消息内容
	 *
	 * @return mixed
	 */
	private static function ServerChan($title, $content) {
		$ret = false;
		try{
			// TODO：一天仅可发送不超过500条
			$url = 'https://sc.ftqq.com/'.Helpers::systemConfig()['server_chan_key'].'.send?text='.$title.'&desp='.urlencode($content);
			$result = json_decode(Curl::send($url), true);
			if(empty(Helpers::systemConfig()['server_chan_key'])){
				$result = new stdClass();
				$result->errno = true;
				$result->errmsg = "未正确配置ServerChan";
			}
			if($result != null && !$result->errno){
				Helpers::addNotificationLog($title, $content, 2);
				$ret = true;
			}else{
				Helpers::addNotificationLog($title, $content, 2, 'admin', 1, $result? $result->errmsg : '未知');
			}
		}catch(Exception $e){
			Log::error('ServerChan消息推送异常：'.$e);
		}

		return $ret;
	}

	/**
	 * Bark推送消息
	 *
	 * @param  string  $title    消息标题
	 * @param  string  $content  消息内容
	 *
	 * @return mixed
	 */
	private static function Bark($title, $content) {
		$ret = false;
		try{
			$url = 'https://api.day.app/'.Helpers::systemConfig()['bark_key'].'/'.$title.'/'.$content;
			$result = json_decode(Curl::send($url), true);
			if($result){
				if($result->code == 200){
					Helpers::addNotificationLog($title, $content, 3);
					$ret = true;
				}else{
					Helpers::addNotificationLog($title, $content, 3, 'admin', $result->message);
				}
			}
		}catch(Exception $e){
			Log::error('Bark消息推送异常：'.$e);
		}

		return $ret;
	}
}
