<?php

namespace App\Http\Middleware;

use App\Models\NodeAuth;
use App\Models\SsNode;
use Closure;
use Response;

class WebApi {
	/**
	 * Handle an incoming request.
	 *
	 * @param           $request
	 * @param  Closure  $next
	 *
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		$id = $request->id;
		$key = $request->header('key');
		$time = $request->header('timestamp');

		if($key === null){	// 未提供 key
			return Response::json([
				"status"  => "fail",
				"code"    => 404,
				"data"    => "",
				"message" => "Your key is null"
			]);
		}elseif($id === null){// 未提供 node
			return Response::json([
				"status"  => "fail",
				"code"    => 404,
				"data"    => "",
				"message" => "Your Node Id is null"
			]);
		}

		$node = SsNode::query()->whereId($id)->first();
		if(!$node){// node不存在
			return Response::json([
				"status"  => "fail",
				"code"    => 404,
				"data"    => "",
				"message" => "Unknown Node"
			]);
		}

		$nodeAuth = NodeAuth::query()->whereNodeId($id)->first();
		if(!$nodeAuth || $key != $nodeAuth->key){// key不存在/不匹配
			return Response::json([
				"status"  => "fail",
				"code"    => 404,
				"data"    => "",
				"message" => "Token is invalid"
			]);
		}

		if(abs($time - time()) >= 300){//时差超过5分钟
			return Response::json([
				"status"  => "fail",
				"code"    => 404,
				"data"    => "",
				"message" => "Please resynchronize the server time!"
			]);
		}

		return $next($request);
	}
}
