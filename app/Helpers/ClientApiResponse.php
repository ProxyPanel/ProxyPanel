<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait ClientApiResponse
{
    private static $client;

    public function __construct(Request $request)
    {
        if (str_contains($request->userAgent(), 'bob_vpn')) {
            self::$client = 'bob';
        }
    }

    public function setClient($client)
    {
        self::$client = $client;
    }

    public function succeed($data = null, $addition = null, $codeResponse = ResponseEnum::HTTP_OK): JsonResponse
    {
        return $this->jsonResponse(1, $codeResponse, $data, $addition);
    }

    private function jsonResponse($status, $codeResponse, $data = null, $addition = null): JsonResponse
    {
        [$code, $message] = $codeResponse;
        $code = $code > 1000 ? (int) ($code / 1000) : $code;
        if (self::$client === 'bob') { // bob 客户端 返回格式
            $result = ['ret' => $status, 'msg' => $message, 'data' => $data];

            if (isset($addition)) {
                $result = array_merge($result, $addition);
            }
        } else { // ProxyPanel client api 规范格式
            if (isset($data, $addition) && is_array($data)) {
                $data = array_merge($data, $addition);
            }

            $result = ['status' => $status ? 'success' : 'fail', 'code' => $code, 'message' => $message, 'data' => $data ?? $addition];
        }

        return response()->json($result, $code, ['content-type' => 'application/json']);
    }

    public function failed($codeResponse = ResponseEnum::HTTP_ERROR, $data = null, $addition = null): JsonResponse
    {
        return $this->jsonResponse(0, $codeResponse, is_array($data) ? $data[0] : $data, $addition);
    }
}
