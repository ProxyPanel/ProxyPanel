<?php

namespace App\Http\Middleware;

use App\Components\Helpers;
use Closure;
use Illuminate\Http\Request;

class isMaintenance {
	/**
	 * 校验是否开启维护模式
	 *
	 * @param  Request  $request
	 * @param  Closure  $next
	 *
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		if(Helpers::systemConfig()['maintenance_mode']){
			return response()->view('auth.maintenance', [
				'message' => Helpers::systemConfig()['maintenance_content'],
				'time'    => Helpers::systemConfig()['maintenance_time']?: '0'
			]);
		}

		return $next($request);
	}
}
