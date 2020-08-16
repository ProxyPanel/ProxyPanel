<?php

namespace App\Jobs\VNet;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class editUser implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	private static $data;
	private $nodes;

	public function __construct(User $user, $nodes) {
		$this->nodes = $nodes;
		self::$data = [
			'uid'         => $user->id,
			'port'        => $user->port,
			'passwd'      => $user->passwd,
			'speed_limit' => $user->speed_limit,
			'enable'      => $user->enable,
		];
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

		$client->post('/api/user/edit', ['body' => json_encode(self::$data)]);
	}
}
