<?php

namespace App\Jobs\VNet;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class addUser implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	private static $data;
	private static $url;
	private $nodes;

	public function __construct(User $users, $nodes) {
		$this->nodes = $nodes;
		if($users->count() > 1){
			self::$url = '/api/v2/user/add/list';
		}else{
			self::$url = '/api/user/add';
		}
		$data = [];
		foreach($users as $user){
			$data[] = [
				'uid'         => $user->id,
				'port'        => $user->port,
				'passwd'      => $user->passwd,
				'speed_limit' => $user->speed_limit,
				'enable'      => $user->enable,
			];
		}
		self::$data = $data;
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

		$client->post(self::$url, ['body' => json_encode(self::$data)]);
	}
}
