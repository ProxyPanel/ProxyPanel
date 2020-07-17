<?php

namespace App\Http\Middleware;

use App\Models\NodeAuth;
use App\Models\SsNode;
use Closure;
use Illuminate\Http\JsonResponse;
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

		if(!isset($key)){// 未提供 key
			return $this->returnData('Your key is null!');
		}

		if(!isset($id)){// 未提供 node
			return $this->returnData('Your Node Id is null!');
		}

		$node = SsNode::query()->whereId($id)->first();
		if(!$node){// node不存在
			return $this->returnData('Unknown Node!');
		}

		$nodeAuth = NodeAuth::query()->whereNodeId($id)->first();
		if(!$nodeAuth || $key != $nodeAuth->key){// key不存在/不匹配
			return $this->returnData('Token is invalid!');
		}

		if(abs($time - time()) >= 300){// 时差超过5分钟
			return $this->returnData('Please resynchronize the server time!');
		}

		return $next($request);
	}

	// 返回数据
	public function returnData($message): JsonResponse {
		return Response::json(['status' => 'fail', 'code' => 404, 'data' => '', 'message' => $message]);
	}
}
