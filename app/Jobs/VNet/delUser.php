<?php

namespace App\Jobs\VNet;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class delUser implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	private static $userIds;
	private $nodes;

	public function __construct($userIds, $nodes) {
		self::$userIds = $userIds;
		$this->nodes = $nodes;
	}

	public function handle(): void {
		foreach($this->nodes as $node){
			self::send(($node->server?: $node->ip).':'.$node->push_port, $node->auth->secret);
		}
	}

	private static function send($host, $secret): void {
		$client = new Client([
			'base_uri' => $host,
			'timeout'  => 15,
			'headers'  => [
				'secret'       => $secret,
				'content-type' => 'application/json'
			]
		]);

		if(is_array(self::$userIds)){
			$client->post('/api/v2/user/del/list', ['body' => json_encode(self::$userIds)]);
		}else{
			$client->post('/api/user/del/'.self::$userIds);
		}
	}
}
