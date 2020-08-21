<?php

namespace App\Jobs\VNet;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class reloadNode implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	private $nodes;

	public function __construct($nodes) {
		$this->nodes = $nodes;
	}

	public function handle(): bool {
		$allSuccess = true;
		foreach($this->nodes as $node){
			$ret = $this->send(($node->server?: $node->ip).':'.$node->push_port, $node->auth->secret, [
				'id'             => $node->id,
				'port'           => (string) $node->port,
				'passwd'         => $node->passwd?: '',
				'method'         => $node->method,
				'protocol'       => $node->protocol,
				'obfs'           => $node->obfs,
				'protocol_param' => $node->protocol_param,
				'obfs_param'     => $node->obfs_param?: '',
				'push_port'      => $node->push_port,
				'single'         => $node->single,
				'secret'         => $node->auth->secret,
				//			'is_udp'         => $node->is_udp,
				//			'speed_limit'    => $node->speed_limit,
				//			'client_limit'   => $node->client_limit,
				//			'redirect_url'   => (string) sysConfig('redirect_url')
			]);

			if(!$ret){
				$allSuccess = false;
			}
		}

		return $allSuccess;
	}

	public function send($host, $secret, $data): bool {
		$client = new Client([
			'base_uri' => $host,
			'timeout'  => 15,
			'headers'  => ['secret' => $secret]
		]);

		$ret = $client->post('api/v2/node/reload', ['json' => $data]);
		if($ret->getStatusCode() == 200){
			$message = json_decode($ret->getBody(), true);
			if(array_key_exists('success', $message) && array_key_exists('content', $message)){
				if($message['success']){
					return true;
				}
				Log::error('重载节点失败：'.$host.' 反馈：'.$message['content']);
			}
		}
		Log::error('重载节点失败url: '.$host);
		return false;
	}
}
