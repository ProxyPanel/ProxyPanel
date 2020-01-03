<?php

namespace App\Components;

class Curl
{
	/**
	 * @param string $url  请求地址
	 * @param array  $data 数据，如果有数据则用POST请求
	 *
	 * @return mixed
	 */
	public static function send($url, $data = [])
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_URL, $url);

		if($data){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}

		$result = curl_exec($ch);
		curl_close($ch);

		return $result;
	}
}