<?php

namespace Tests\Unit\Helpers;

use App\Helpers\ClientApiResponse;
use App\Helpers\ResponseEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ReflectionMethod;
use Tests\TestCase;

/**
 * 客户端 API 的响应契约：$client 是 private static，用例必须自己复位，否则跨用例泄漏。
 */
class ClientApiResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->resetClient();
    }

    protected function tearDown(): void
    {
        $this->resetClient();

        parent::tearDown();
    }

    /** $client 是静态属性，复位只能借任意实例的公开 setter 做 */
    private function resetClient(): void
    {
        $this->subject()->setClient('clash');
    }

    private function subject(?string $userAgent = null): object
    {
        $request = Request::create('/api/v1/user/self', 'GET', [], [], [], [
            'HTTP_USER_AGENT' => $userAgent ?? 'clash-verge/1.7.0',
        ]);

        return new class($request)
        {
            use ClientApiResponse;
        };
    }

    private function body(JsonResponse $response): array
    {
        return json_decode($response->getContent(), true);
    }

    public function test_non_bob_response_uses_the_named_field_set(): void
    {
        $payload = ['token' => 'abc', 'expire_at' => 1700000000];

        $response = $this->subject()->succeed($payload);

        $body = $this->body($response);
        $this->assertArrayNotHasKey('ret', $body);
        $this->assertSame('success', $body['status']);
        $this->assertIsString($body['status']);
        $this->assertSame(200, $body['code']);
        $this->assertIsInt($body['code']);
        $this->assertSame(ResponseEnum::HTTP_OK[1], $body['message']);
        $this->assertSame($payload, $body['data']);
        $this->assertSame('application/json', $response->headers->get('content-type'));
    }

    public function test_bob_user_agent_switches_to_the_ret_and_msg_shape(): void
    {
        $response = $this->subject('bob_vpn/2.1.0 (Android)')->succeed(['sub' => 'https://example.com/sub']);

        $body = $this->body($response);
        $this->assertArrayNotHasKey('status', $body);
        $this->assertArrayNotHasKey('code', $body);
        $this->assertSame(1, $body['ret']);
        $this->assertSame(ResponseEnum::HTTP_OK[1], $body['msg']);
        $this->assertSame(['sub' => 'https://example.com/sub'], $body['data']);
    }

    public function test_business_code_above_1000_is_divided_by_a_thousand(): void
    {
        $response = $this->subject()->succeed(null, null, ResponseEnum::USER_SERVICE_LOGIN_SUCCESS);

        // [200200, '登录成功'] → code 200，同时是 HTTP 状态码。
        $this->assertSame(200, $this->body($response)['code']);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_null_data_falls_back_to_the_addition_payload(): void
    {
        $addition = ['version' => '2.0.0', 'node_list' => []];

        $body = $this->body($this->subject()->succeed(null, $addition));

        $this->assertSame($addition, $body['data']);
    }

    public function test_failed_reports_fail_status(): void
    {
        $body = $this->body($this->subject()->failed());

        $this->assertSame('fail', $body['status']);
        $this->assertSame(200, $body['code']);
        $this->assertSame(ResponseEnum::HTTP_ERROR[1], $body['message']);
        $this->assertNull($body['data']);
    }

    public function test_failed_bob_response_reports_zero_ret(): void
    {
        $body = $this->body($this->subject('bob_vpn/2.1.0')->failed(ResponseEnum::USER_SERVICE_LOGIN_ERROR));

        $this->assertSame(0, $body['ret']);
        $this->assertSame(ResponseEnum::USER_SERVICE_LOGIN_ERROR[1], $body['msg']);
    }

    public function test_failed_sends_the_whole_validation_error_list_to_the_client(): void
    {
        // 现网调用方传的都是 `$validator->errors()->all()`（Api/Client/AuthController:38,94
        // 与 Api/WebApi/CoreController 的四处）。改之前这条链路是「取 [0] 丢掉其余，再被
        // array|bool|null 形参折成 bool」，客户端只拿到 "data":true，等于没告诉用户哪条填错。
        $body = $this->body($this->subject()->failed(ResponseEnum::CLIENT_PARAMETER_ERROR, ['邮箱格式不正确', '密码过短']));

        $this->assertSame('fail', $body['status']);
        $this->assertSame(400, $body['code']);
        $this->assertSame(['邮箱格式不正确', '密码过短'], $body['data']);

        // 裸字符串也不再被强制转换成 bool
        $this->assertSame('邮箱格式不正确', $this->body($this->subject()->failed(ResponseEnum::CLIENT_PARAMETER_ERROR, '邮箱格式不正确'))['data']);

        // bob 那套同样能拿到明细
        $bob = $this->body($this->subject('bob_vpn/2.1.0')->failed(ResponseEnum::CLIENT_PARAMETER_ERROR, ['密码过短']));
        $this->assertSame(['密码过短'], $bob['data']);
    }

    public function test_failed_without_data_keeps_data_null(): void
    {
        $this->assertNull($this->body($this->subject()->failed(ResponseEnum::HTTP_ERROR))['data']);
    }

    public function test_boolean_false_data_does_not_fall_back_to_the_addition(): void
    {
        // `$data ?? $addition` 只在 null 时兜底，所以显式传 false 会把 addition 吞掉。
        // 顺带钉住：succeed() 恒传 $status=1，data 为 false 也照样渲染 status: "success"
        // —— 「成功但结果为假」是合法响应，客户端不能靠 status 判业务真假。
        $body = $this->body($this->subject()->succeed(false, ['errors' => ['email' => '格式不正确']]));

        $this->assertSame('success', $body['status']);
        $this->assertFalse($body['data']);
        $this->assertArrayNotHasKey('errors', $body);
    }

    public function test_negative_status_is_truthy_and_renders_as_success(): void
    {
        // 已知判据缺陷的现状记录，不要依赖它：jsonResponse(int $status, ...) 用 `$status ? 'success' : 'fail'`
        // 真值判定，所以历史上「用 -1 表示失败」的调用会渲染成 {"status":"success"}（实测未登录也报成功）。
        // 契约要求失败一律走 failed()；这条断言只是防止有人把三元判据悄悄改成 === 1 之外的形式而无测试可查。
        $method = new ReflectionMethod($this->subject(), 'jsonResponse');
        $response = $method->invoke($this->subject(), -1, ResponseEnum::CLIENT_HTTP_UNAUTHORIZED);

        $body = $this->body($response);
        $this->assertSame('success', $body['status']);
        $this->assertSame(401, $body['code']);
    }

    public function test_client_flag_is_static_and_shared_across_instances(): void
    {
        // 说明为什么必须复位：任一实例的构造函数都会改写私有静态 $client，另一个实例随后就跟着换格式。
        $bob = $this->subject('bob_vpn/2.1.0');
        $this->assertArrayHasKey('ret', $this->body($bob->succeed(null)));

        // 新的普通 UA 实例并不会把格式切回来，静态标记仍是 bob。
        $this->assertArrayHasKey('ret', $this->body($this->subject()->succeed(null)));

        $this->subject()->setClient('clash');
        $this->assertArrayHasKey('status', $this->body($bob->succeed(null)));
    }
}
