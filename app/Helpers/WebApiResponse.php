<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

trait WebApiResponse
{
    public function succeed(?array $data = null, ?array $addition = null, array $codeResponse = ResponseEnum::HTTP_OK): JsonResponse // 成功
    {
        return $this->jsonResponse('success', $codeResponse, $data, $addition);
    }

    private function jsonResponse(string $status, array $codeResponse, ?array $data = null, ?array $addition = null): JsonResponse // 返回数据
    {
        [$code, $message] = $codeResponse;
        if ($status === 'success') {
            $etag = self::abortIfNotModified($data);
        }
        $code = $code < 1000 ? $code : (int) ($code / 1000);
        $data = compact('status', 'code', 'data', 'message');
        if (isset($addition)) {
            $data = array_merge($data, $addition);
        }

        return response()->json($data, $code, ['ETAG' => $etag ?? '']);
    }

    private static function abortIfNotModified(?array $data): string // 检查数据是否有变动
    {
        $req = request();

        if (! $req->isMethod('GET')) { // Only for "GET" method
            return '';
        }

        $etag = sha1(json_encode($data));
        if (! empty($req->header('IF-NONE-MATCH')) && hash_equals($etag, $req->header('IF-NONE-MATCH'))) {
            abort(304);
        }

        return $etag;
    }

    public function failed(array $codeResponse = ResponseEnum::HTTP_ERROR, ?array $data = null): JsonResponse // 失败
    {
        return $this->jsonResponse('fail', $codeResponse, $data);
    }
}
