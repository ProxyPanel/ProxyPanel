<?php

namespace App\Http\Controllers\Api\WebApi;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Validator;

/**
 * Hysteria2 API控制器
 * 提供Hysteria2 HTTP验证端点.
 */
class Hysteria2Controller extends Controller
{
    private function returnFormat(array $data = [], bool $ok = false): JsonResponse
    {
        return response()->json(['ok' => $ok, ...$data]);
    }

    /**
     * Hysteria2 HTTP验证端点
     * 用于验证用户身份，接收Hysteria2服务端的验证请求
     */
    public function authenticate(Node $node, Request $request): JsonResponse
    {
        // 使用Validator验证输入，更详细地验证各个字段格式
        $validator = Validator::make($request->all(), [
            'auth' => ['required', 'string', 'regex:/^\d+:[^:]+$/'],
            'addr' => ['nullable', 'string', 'regex:/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|\[[0-9a-fA-F:]+\]):\d+$/'],
            'tx' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->returnFormat(['error' => 'Invalid request format']);
        }

        $credentials = $validator->validated();

        // 从auth字段提取端口和密码 (格式: port:passwd)
        $auth = $credentials['auth'];
        $authParts = explode(':', $auth, 2);

        if (count($authParts) < 2) {
            return $this->returnFormat(['error' => 'Invalid auth format']);
        }

        [$port, $password] = $authParts;

        // 根据端口查找用户
        $user = User::where('port', $port)->first();

        if (! $user) {
            return $this->returnFormat(['error' => 'User not found']);
        }

        // 验证用户状态
        if (! $user->enable) {
            return $this->returnFormat(['error' => 'User was disabled']);
        }

        // 验证密码
        if ($user->passwd !== $password) {
            return $this->returnFormat(['error' => 'Password incorrect']);
        }

        if (! $user->nodes()->where('node.id', $node->id)->exists()) {
            return $this->returnFormat(['error' => 'Does not have permission to access this node']);
        }

        // 从addr字段提取客户端IP和端口
        $addr = $credentials['addr'] ?? null;

        // 区分IPv4和IPv6格式来解析IP和端口
        if ($addr) {
            // parse_url 无法直接解析没有 scheme 的地址，所以拼上 //
            $parsed = parse_url('//'.$addr);

            $clientIp = str_replace(['[', ']'], '', $parsed['host'] ?? '');
            $port = $parsed['port'] ?? null;

            if (! filter_var($clientIp, FILTER_VALIDATE_IP)) {
                return $this->returnFormat(['error' => 'Invalid IP address']);
            }

            // 记录在线IP日志
            $node->onlineIps()->create([
                'user_id' => $user->id,
                'ip' => $clientIp,
                'port' => $port,
                'created_at' => time(),
            ]);
        } else {
            Log::error('Hysteria2: addr is null', $credentials);
        }

        return $this->returnFormat(['id' => (string) $user->id], true);
    }
}
