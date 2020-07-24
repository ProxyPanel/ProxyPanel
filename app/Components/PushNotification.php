<?php


namespace App\Components;

use GuzzleHttp\Client;
use Log;

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
		// TODO：一天仅可发送不超过500条
		$client = new Client(['timeout' => 5]);
		$request = $client->get('https://sc.ftqq.com/'.Helpers::systemConfig()['server_chan_key'].'.send?text='.$title.'&desp='.urlencode($content));
		$message = json_decode($request->getBody(), true);
		Log::debug($message);
		// 发送成功
		if($request->getStatusCode() == 200){
			if(!$message['errno']){
				Helpers::addNotificationLog($title, $content, 2);
				return $message;
			}
			// 发送失败
			Helpers::addNotificationLog($title, $content, 2, 'admin', -1, $message? $message['errmsg'] : '未知');
			return false;
		}
		// 发送错误
		Log::debug('ServerChan消息推送异常：'.var_export($request, true));
		return false;
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
		$client = new Client(['timeout' => 5]);
		$request = $client->get('https://api.day.app/'.Helpers::systemConfig()['bark_key'].'/'.$title.'/'.$content);
		$message = json_decode($request->getBody(), true);

		if($request->getStatusCode() == 200){
			// 发送成功
			if($message['code'] == 200){
				Helpers::addNotificationLog($title, $content, 3);
				return $message;
			}
			// 发送失败
			Helpers::addNotificationLog($title, $content, 3, 'admin', -1, $message);
			return false;
		}
		// 发送错误
		Log::debug('Bark消息推送异常：'.var_export($request, true));
		return false;
	}
}
