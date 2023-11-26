<?php

namespace App\Http\Controllers\Api\Client;

use App\Helpers\ClientApiResponse;
use App\Helpers\ResponseEnum;
use App\Services\UserService;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Validator;

use function auth;
use function config;

class AuthController extends Controller
{
    use ClientApiResponse;

    public function __construct(Request $request)
    {
        if (str_contains($request->userAgent(), 'bob_vpn')) {
            $this->setClient('bob');
        }
    }

    public function register(Request $request, UserService $userService): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nickname' => 'required|string|between:2,100',
            'username' => 'required|'.(sysConfig('username_type') ?? 'email').'|max:100|unique:user,username',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return $this->failed(ResponseEnum::CLIENT_PARAMETER_ERROR, $validator->errors()->all());
        }
        $data = $validator->validated();

        // 创建新用户
        if ($user = Helpers::addUser($data['username'], $data['password'], (int) sysConfig('default_traffic'), (int) sysConfig('default_days'), null, $data['nickname'])) {
            auth()->login($user, true);

            return $this->succeed([
                'token' => $user->createToken('client')->plainTextToken,
                'expire_in' => time() + config('session.lifetime') * Minute,
                'user' => $userService->getProfile(),
            ], null, ResponseEnum::USER_SERVICE_REGISTER_SUCCESS);
        }

        return $this->failed(ResponseEnum::USER_SERVICE_REGISTER_ERROR);
    }

    public function login(Request $request): JsonResponse
    {
        if (self::$client === 'bob') {
            $rules = [
                'email' => 'required|'.(sysConfig('username_type') ?? 'email'),
                'passwd' => 'required|string|min:6',
            ];
        } else {
            $rules = [
                'username' => 'required|'.(sysConfig('username_type') ?? 'email'),
                'password' => 'required|string|min:6',
            ];
        }
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->failed(ResponseEnum::CLIENT_PARAMETER_ERROR, $validator->errors()->all());
        }

        if (auth()->attempt(['username' => $request->input('username') ?: $request->input('email'), 'password' => $request->input('password') ?: $request->input('passwd')],
            true)) {
            $user = auth()->user();
            if ($user && $user->status === -1) {
                return $this->failed(ResponseEnum::CLIENT_HTTP_UNAUTHORIZED_BLACKLISTED);
            }
            if (self::$client === 'bob') {
                $request->session()->put('uid', $user->id);
            }

            return $this->succeed([
                'token' => $user->createToken('client')->plainTextToken,
                'expire_in' => time() + config('session.lifetime') * Minute,
                'user' => (new UserService)->getProfile(),
            ], null, ResponseEnum::USER_SERVICE_LOGIN_SUCCESS);
        }

        return $this->failed(ResponseEnum::SERVICE_LOGIN_ACCOUNT_ERROR);
    }

    public function logout(Request $request): JsonResponse
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->failed(ResponseEnum::USER_SERVICE_LOGOUT_SUCCESS);
    }
}
