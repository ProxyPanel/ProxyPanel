<?php

namespace App\Jobs\VNet;

use App\Models\Node;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class reloadNode implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	private static $host;
	private static $secret;
	private static $data;

	public function __construct(Node $node) {
		self::$host = ($node->server?: $node->ip).':'.$node->push_port;
		self::$secret = $node->auth->secret;
		self::$data = [
			'id'           => $node->id,
			'method'       => $node->method,
			'protocol'     => $node->protocol,
			'obfs'         => $node->obfs,
			'obfs_param'   => $node->obfs_param?: '',
			'is_udp'       => $node->is_udp,
			'speed_limit'  => $node->speed_limit,
			'client_limit' => $node->client_limit,
			'single'       => $node->single,
			'port'         => (string) $node->port,
			'passwd'       => $node->passwd?: '',
			'push_port'    => $node->push_port,
			'secret'       => $node->auth->secret,
			'redirect_url' => sysConfig('redirect_url')
		];
	}

	public function handle(): void {
		$client = new Client([
			'base_uri' => self::$host,
			'timeout'  => 15,
			'headers'  => [
				'secret'       => self::$secret,
				'content-type' => 'application/json'
			]
		]);

		$client->post('/api/v2/node/reload', ['body' => json_encode(self::$data)]);
	}
}
