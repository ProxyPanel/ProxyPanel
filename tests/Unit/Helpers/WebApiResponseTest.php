<?php

namespace Tests\Unit\Helpers;

use App\Helpers\ResponseEnum;
use App\Helpers\WebApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ReflectionMethod;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * 节点侧 API 的响应契约：ETAG 由 payload 哈希而来，仅 GET 计算，未命中缓存判据时为空串。
 */
class WebApiResponseTest extends TestCase
{
    /**
     * 被测壳子：trait 本身不能实例化，匿名类是最小的挂接方式。
     * 没有另建 fixture 文件，避免测试目录里混进非测试类。
     */
    private function subject(): object
    {
        return new class
        {
            use WebApiResponse;
        };
    }

    /** 把容器里的当前请求换成指定方法与头部，request()/abort() 都读它 */
    private function bindRequest(string $method = 'GET', array $headers = []): void
    {
        $server = [];
        foreach ($headers as $name => $value) {
            $server['HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
        }

        $this->app->instance('request', Request::create('/web/v1/user/list', $method, [], [], [], $server));
    }

    public function test_success_payload_is_hashed_into_the_etag_header(): void
    {
        $this->bindRequest();
        $payload = [['id' => 1, 'u' => 'hash'], ['id' => 2, 'u' => 'hash2']];

        $response = $this->subject()->succeed($payload);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(sha1(json_encode($payload)), $response->headers->get('ETAG'));
        $this->assertNotEmpty($response->headers->get('ETAG'));

        $body = json_decode($response->getContent(), true);
        $this->assertSame('success', $body['status']);
        $this->assertSame(200, $body['code']);
        $this->assertSame(ResponseEnum::HTTP_OK[1], $body['message']);
        $this->assertSame($payload, $body['data']);
    }

    public function test_etag_is_stable_for_identical_payloads_and_differs_otherwise(): void
    {
        $this->bindRequest();
        $subject = $this->subject();

        $first = $subject->succeed(['node' => 3, 'users' => 12]);
        $same = $subject->succeed(['node' => 3, 'users' => 12]);
        $other = $subject->succeed(['node' => 3, 'users' => 13]);

        $this->assertSame($first->headers->get('ETAG'), $same->headers->get('ETAG'));
        $this->assertNotSame($first->headers->get('ETAG'), $other->headers->get('ETAG'));
    }

    public function test_non_get_requests_carry_an_empty_etag_string(): void
    {
        // 钉住现状：abortIfNotModified() 对非 GET 返回空串，所以这里是 ETAG: ''，而不是没有这个头。
        $this->bindRequest('POST');

        $response = $this->subject()->succeed(['traffic' => 1024]);

        $this->assertTrue($response->headers->has('ETAG'));
        $this->assertSame('', $response->headers->get('ETAG'));
    }

    public function test_get_with_matching_if_none_match_aborts_with_304(): void
    {
        $payload = ['server' => ['rate' => 1.0]];
        $this->bindRequest('GET', ['IF-NONE-MATCH' => sha1(json_encode($payload))]);

        try {
            $this->subject()->succeed($payload);
            $this->fail('未变更的 GET 应当以 304 中断，不应该渲染响应体。');
        } catch (HttpException $exception) {
            $this->assertSame(304, $exception->getStatusCode());
        }
    }

    public function test_get_with_stale_if_none_match_returns_the_new_etag(): void
    {
        $payload = ['server' => ['rate' => 1.0]];
        $this->bindRequest('GET', ['IF-NONE-MATCH' => sha1(json_encode(['stale' => true]))]);

        $response = $this->subject()->succeed($payload);

        $this->assertSame(sha1(json_encode($payload)), $response->headers->get('ETAG'));
        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_failure_response_reports_fail_and_an_empty_etag(): void
    {
        // 失败分支不进 abortIfNotModified()，$etag 未赋值，经 `$etag ?? ''` 落成空串。
        $this->bindRequest();

        $response = $this->subject()->failed(ResponseEnum::CLIENT_HTTP_UNAUTHORIZED);

        $body = json_decode($response->getContent(), true);
        $this->assertSame('fail', $body['status']);
        $this->assertSame(401, $body['code']);
        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('', $response->headers->get('ETAG'));
        $this->assertNull($body['data']);
    }

    public function test_business_code_is_scaled_down_to_an_http_status(): void
    {
        $this->bindRequest();
        $subject = $this->subject();

        $ok = $subject->succeed(null, null, ResponseEnum::USER_SERVICE_LOGIN_SUCCESS);
        $notFound = $subject->failed(ResponseEnum::CLIENT_NOT_FOUND_HTTP_ERROR);

        // 200200 → 200，400001 → 400：业务码千位以上除以 1000，同时是 HTTP 状态码。
        $this->assertSame(200, json_decode($ok->getContent(), true)['code']);
        $this->assertSame(200, $ok->getStatusCode());
        $this->assertSame(400, json_decode($notFound->getContent(), true)['code']);
        $this->assertSame(400, $notFound->getStatusCode());
    }

    public function test_addition_merges_without_overwriting_the_envelope_keys(): void
    {
        $this->bindRequest();
        $payload = ['info' => 'sub-url'];

        $response = $this->subject()->succeed($payload, ['data' => 'hijack', 'extra' => 1]);

        $body = json_decode($response->getContent(), true);
        // `$data += $addition` 只补缺键，所以 status/code/data/message 都抢不走。
        $this->assertSame($payload, $body['data']);
        $this->assertSame(1, $body['extra']);
        $this->assertSame('success', $body['status']);
        // 钉住现状：ETAG 哈希的是压缩前的 data 参数，addition 不参与，所以带不带 addition 都是同一个 ETag。
        $this->assertSame(sha1(json_encode($payload)), $response->headers->get('ETAG'));
    }

    public function test_negative_status_is_stringified_and_still_truthy(): void
    {
        // 已知缺陷的现状记录，不要依赖它：AGENTS.md 让调用方「失败一律走 failed()」而不是 jsonResponse(-1, ...)。
        // WebApiResponse::jsonResponse 的 $status 是 string 形参，-1 被强制转成 '-1'：既不是 'success'（ETAG 因此为空），
        // 也不是 'fail'，节点侧拿到的字符串 '-1' 在 JS/Shell 判真值时依旧为真。'success' 那种渲染发生在
        // ClientApiResponse（int 形参 + 真值三元），见 ClientApiResponseTest。
        $this->bindRequest();

        $method = new ReflectionMethod($this->subject(), 'jsonResponse');
        $response = $method->invoke($this->subject(), -1, ResponseEnum::HTTP_OK, ['a' => 1]);

        $body = json_decode($response->getContent(), true);
        $this->assertSame('-1', $body['status']);
        $this->assertTrue((bool) $body['status']);
        $this->assertSame('', $response->headers->get('ETAG'));
    }
}
