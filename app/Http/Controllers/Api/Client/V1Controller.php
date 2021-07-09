<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Goods;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;

class V1Controller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'shop']]);
        auth()->shouldUse('api');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|'.(sysConfig('username_type') ?? 'email'),
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['ret' => 0, 'msg' => $validator->errors()->all()], 422);
        }

        if ($token = auth()->attempt($validator->validated())) {
            return $this->createNewToken($token);
        }

        return response()->json(['ret' => 0, 'msg' => '登录信息错误'], 401);
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'ret'          => 1,
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60,
            'user'         => auth()->user()->profile(),
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|between:2,100',
            'username' => 'required|'.(sysConfig('username_type') ?? 'email').'|max:100|unique:user,username',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->all(), 400);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => $request->password]
        ));

        return response()->json(['ret' => 1, 'user' => $user], 201);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['ret' => 1]);
    }

    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    public function userProfile()
    {
        return response()->json(auth()->user()->profile());
    }

    public function nodeList(int $id = null)
    {
        $user = auth()->user();
        $nodes = $user->nodes()->get();
        if (isset($id)) {
            $node = $nodes->find($id);

            if (empty($node)) {
                return response()->json([], 204);
            }

            return response()->json($node->config($user));
        }
        $servers = [];
        foreach ($nodes as $node) {
            $servers[] = $node->config($user);
        }

        return response()->json($servers);
    }

    public function shop()
    {
        $shop = Goods::whereStatus(1)->where('type', '<=', '2')->orderByDesc('type')->orderByDesc('sort')->get();

        return response()->json($shop);
    }
}
