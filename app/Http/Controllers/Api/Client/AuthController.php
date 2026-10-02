<?php

namespace App\Http\Controllers\Api\Client;

use App\Helpers\ClientApiResponse;
use App\Helpers\ResponseEnum;
use App\Models\User;
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
        if ($user = Helpers::addUser($data['username'], $data['password'], MiB * sysConfig('default_traffic'), (int) sysConfig('default_days'), null, $data['nickname'])) {
            auth()->login($user, true);
            $userService->setUser($user);

            return $this->succeed([
                'token' => $this->issueToken($user),
                'expire_in' => $this->tokenExpireIn(),
                'user' => $userService->getProfile(),
            ], null, ResponseEnum::USER_SERVICE_REGISTER_SUCCESS);
        }

        return $this->failed(ResponseEnum::USER_SERVICE_REGISTER_ERROR);
    }

    /**
     * 签发客户端 token，并按 config('sanctum.client_token_ttl') 写 expires_at；留空即不过期。
     */
    private function issueToken(User $user): string
    {
        $minutes = config('sanctum.client_token_ttl');

        return $user->createToken('client', ['*'], $minutes ? now()->addMinutes($minutes) : null)->plainTextToken;
    }

    /**
     * 告知客户端的到期时刻；未配置 TTL 时回落到会话时长，是建议轮换的软时刻。
     */
    private function tokenExpireIn(): int
    {
        return time() + (config('sanctum.client_token_ttl') ?: config('session.lifetime')) * Minute;
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
                'token' => $this->issueToken($user),
                'expire_in' => $this->tokenExpireIn(),
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
