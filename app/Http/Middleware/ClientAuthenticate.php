<?php

namespace App\Http\Middleware;

use App\Helpers\ClientApiResponse;
use App\Helpers\ResponseEnum;
use Closure;
use Illuminate\Http\Request;

class ClientAuthenticate
{
    use ClientApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $session = $request->session();
        if (isset($session) && ! $session->get('uid')) {
            return $this->jsonResponse(-1, ResponseEnum::USER_SERVICE_LOGIN_ERROR);
        }

        return $next($request);
    }
}
