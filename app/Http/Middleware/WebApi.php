<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;
use Response;

class WebApi extends Middleware {
	/**
	 * Handle an incoming request.
	 *
	 * @param  Request  $request
	 * @param  Closure  $next
	 *
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		$key = $request->input('key');
		// 未提供 key
		if($key === null){
			return Response::json([
				                      'ret'  => 0,
				                      'data' => 'Your key is null'
			                      ]);
		}

		if(!in_array($key, env('WEB_API_KEY'))){
			// key 不存在
			return Response::json([
				                      'ret'  => 0,
				                      'data' => 'Token is invalid'
			                      ]);
		}

		if(env('WEB_API') == false){
			// 主站不提供 Webapi
			return Response::json([
				                      'ret'  => 0,
				                      'data' => 'We regret this service is temporarily unavailable'
			                      ]);
		}

		return $next($request);
	}
}
