<?php

namespace Tests\Unit\Utils;

use App\Utils\Payments\Stripe as StripeGateway;
use ReflectionClass;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;
use Stripe\Source;
use Stripe\Stripe;
use Tests\TestCase;

/**
 * 固定 Stripe API 版本这件事必须被锁住.
 *
 * 背景：stripe-php 会把「自己的内置默认版本」当作 Stripe-Version 头随每个请求发出，而本项目
 * 没有在任何地方调用 setApiVersion，所以升级 SDK 就会把所有支付请求切到新 API 版本上
 * （v15.10.0 发 2024-06-20，v21.3.2 发 2026-08-26.dahlia）。网关构造函数里的固定就是为此存在的，
 * 一旦被误删，下面这个断言会直接失败。
 */
class StripeApiVersionTest extends TestCase
{
    /** 网关固定的版本，与升级 stripe-php 之前的 SDK 默认值一致 */
    private const PINNED_VERSION = '2024-06-20';

    private array $captured = [];

    private ?string $originalKey;

    private ?string $originalVersion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalKey = Stripe::getApiKey();
        $this->originalVersion = Stripe::getApiVersion();
    }

    protected function tearDown(): void
    {
        Stripe::setApiKey($this->originalKey);
        Stripe::setApiVersion($this->originalVersion);

        parent::tearDown();
    }

    public function test_网关构造后发出的请求带着固定的_api_版本(): void
    {
        new StripeGateway;
        Stripe::setApiKey('sk_test_fake'); // 设置里没有 key 时构造函数会把它置空，这里补回来只为把请求发出去

        $request = $this->captureSourceCreation();

        $this->assertSame('post', $request['method']);
        $this->assertSame('https://api.stripe.com/v1/sources', $request['absUrl']);
        $this->assertSame(self::PINNED_VERSION, $this->versionHeader($request['headers']));
    }

    public function test_网关里固定的版本就是升级前_sdk_的默认版本(): void
    {
        $this->assertSame(
            self::PINNED_VERSION,
            (new ReflectionClass(StripeGateway::class))->getConstant('API_VERSION')
        );
    }

    /** 用假 HTTP 客户端发起一次 Source 创建（支付宝/微信分支走的就是这个接口），拿到实际请求 */
    private function captureSourceCreation(): array
    {
        $this->captured = [];

        ApiRequestor::setHttpClient(new class($this->captured) implements ClientInterface
        {
            public function __construct(public array &$captured) {}

            public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null): array
            {
                $this->captured[] = compact('method', 'absUrl', 'headers', 'params', 'apiMode');

                return ['{"id":"src_fake","object":"source","redirect":{"url":"https://example.test/pay"}}', 200, []];
            }
        });

        Source::create([
            'amount' => 1000,
            'currency' => 'cny',
            'type' => 'alipay',
            'redirect' => ['return_url' => 'https://example.test/return'],
        ]);

        return $this->captured[0];
    }

    private function versionHeader(array $headers): string
    {
        foreach ($headers as $header) {
            if (stripos($header, 'stripe-version:') === 0) {
                return trim(substr($header, strlen('stripe-version:')));
            }
        }

        return '';
    }
}
