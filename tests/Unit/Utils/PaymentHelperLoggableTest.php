<?php

namespace Tests\Unit\Utils;

use App\Utils\Library\PaymentHelper;
use PHPUnit\Framework\TestCase;

/**
 * 报文脱敏：凭证类字段命中即打码，嵌套递归，超长值截断。
 */
class PaymentHelperLoggableTest extends TestCase
{
    public function test_masks_credentials_and_leaves_business_fields(): void
    {
        $masked = PaymentHelper::loggable([
            'out_trade_no' => 'AbCd1234',
            'money' => '9.90',
            'sign' => 'RAW-SIGNATURE',
            'sign_type' => 'MD5',
            'token' => 'PLATFORM-TOKEN',
            'card_no' => '4111111111111111',
            'cvv' => '123',
            'password' => 'p@ss',
        ]);

        $this->assertStringContainsString("'out_trade_no' => 'AbCd1234'", $masked);
        $this->assertStringContainsString("'money' => '9.90'", $masked);
        foreach (['RAW-SIGNATURE', 'PLATFORM-TOKEN', '4111111111111111', 'p@ss'] as $secret) {
            $this->assertStringNotContainsString($secret, $masked);
        }
    }

    public function test_masks_payer_identity_fields_too(): void
    {
        // 付款人身份字段（支付宝 buyer_logon_id / payer_id、Stripe customer_email、微信 openid）也不留档
        $masked = PaymentHelper::loggable([
            'buyer_logon_id' => 'buyer@aliyun.com',
            'payer_id' => '2088123456789012',
            'customer_email' => 'shopper@example.com',
            'openid' => 'o-abc123456789',
            'phone' => '13800001111',
            'out_trade_no' => 'KEEPME',
        ]);

        foreach (['buyer@aliyun.com', '2088123456789012', 'shopper@example.com', 'o-abc123456789', '13800001111'] as $pii) {
            $this->assertStringNotContainsString($pii, $masked);
        }
        $this->assertStringContainsString('KEEPME', $masked);
    }

    public function test_masks_inside_nested_payloads(): void
    {
        $masked = PaymentHelper::loggable(['data' => ['amount' => 100, 'secret_key' => 'TOP-SECRET']]);

        $this->assertStringNotContainsString('TOP-SECRET', $masked);
        $this->assertStringContainsString('100', $masked);
    }

    public function test_truncates_overlong_values(): void
    {
        $masked = PaymentHelper::loggable(['alipay_cert' => str_repeat('A', 200)]);

        $this->assertStringNotContainsString(str_repeat('A', 200), $masked);
        $this->assertStringContainsString(str_repeat('A', 64).'...', $masked);
    }

    public function test_null_payload_stays_renderable(): void
    {
        $this->assertSame('NULL', PaymentHelper::loggable(null));
    }
}
