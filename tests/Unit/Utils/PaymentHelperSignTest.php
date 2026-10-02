<?php

namespace Tests\Unit\Utils;

use App\Utils\Library\PaymentHelper;
use PHPUnit\Framework\TestCase;

/**
 * 支付宝式 MD5 签名：剔除 sign/sign_type、过滤空值、按键排序后再拼密钥。
 */
class PaymentHelperSignTest extends TestCase
{
    /** 手写拼接原文 out_trade_no=NO123&subject=OtakuCloud&total_amount=9.90testkey 的 md5 */
    private const EXPECTED_ALIPAY_SIGN = '55f768fc430ff6608f7070905c855fa4';

    /** 手写拼接原文 a=1&b=2&c=3secret 的 md5 */
    private const EXPECTED_ABC_SIGN = '44d18b899e05f82c1cc4ce22bf5df09b';

    /**
     * 独立算出的期望值：把业务字段按 k=v 以 & 连接、尾部接密钥后取 md5，
     * 与实现结果一致才算这条规则被锁住。
     */
    public function test_signature_matches_independently_computed_md5(): void
    {
        $data = [
            'total_amount' => '9.90',
            'subject' => 'OtakuCloud',
            'out_trade_no' => 'NO123',
        ];

        $this->assertSame(self::EXPECTED_ALIPAY_SIGN, PaymentHelper::aliStyleSign($data, 'testkey'));
    }

    /** sign 与 sign_type 必须在签名前被剔除：网关会原样回传它们，留着就永远验不过 */
    public function test_sign_and_sign_type_are_excluded(): void
    {
        $clean = [
            'out_trade_no' => 'NO123',
            'subject' => 'OtakuCloud',
            'total_amount' => '9.90',
        ];

        foreach ([
            ['sign' => 'DEADBEEF', 'sign_type' => 'MD5'],
            ['sign' => '', 'sign_type' => ''],
            ['sign' => self::EXPECTED_ALIPAY_SIGN, 'sign_type' => 'RSA2'],
        ] as $extra) {
            $this->assertSame(
                self::EXPECTED_ALIPAY_SIGN,
                PaymentHelper::aliStyleSign(array_merge($extra, $clean), 'testkey'),
                '这两个键的内容不能影响签名'
            );
        }

        // 但内容里出现同名的「别的」键（例如易支付的 sign_type 变体）仍会参与签名
        $this->assertNotSame(
            self::EXPECTED_ALIPAY_SIGN,
            PaymentHelper::aliStyleSign(array_merge($clean, ['sign_value' => 'x']), 'testkey')
        );
    }

    /** 键按 SORT_STRING 排序，所以数组的插入顺序不该影响签名 */
    public function test_key_order_does_not_matter(): void
    {
        $this->assertSame(
            self::EXPECTED_ALIPAY_SIGN,
            PaymentHelper::aliStyleSign([
                'subject' => 'OtakuCloud',
                'total_amount' => '9.90',
                'out_trade_no' => 'NO123',
            ], 'testkey')
        );

        $this->assertSame(
            self::EXPECTED_ALIPAY_SIGN,
            PaymentHelper::aliStyleSign([
                'total_amount' => '9.90',
                'out_trade_no' => 'NO123',
                'subject' => 'OtakuCloud',
            ], 'testkey')
        );

        $this->assertSame(self::EXPECTED_ABC_SIGN, PaymentHelper::aliStyleSign(['c' => 3, 'a' => 1, 'b' => 2], 'secret'));
    }

    /**
     * 排序是字符串序而不是数值序：'10' 排在 '2' 前面（手写原文 10=ten&2=twok 的 md5）。
     * 有人把它改成 ksort($data)（默认 SORT_REGULAR）就会在这类键名上翻车。
     */
    public function test_keys_are_sorted_as_strings_not_numerically(): void
    {
        $this->assertSame(
            'b423d3a5a4f81b91d89ed569e2499972',
            PaymentHelper::aliStyleSign(['2' => 'two', '10' => 'ten'], 'k')
        );
        $this->assertSame(
            'b423d3a5a4f81b91d89ed569e2499972',
            PaymentHelper::aliStyleSign(['10' => 'ten', '2' => 'two'], 'k')
        );
    }

    /**
     * $filter=true 走的是 array_filter 的默认语义：'' / null / 0 / '0' / false 都算空值，
     * 一并剔除；而 ' '（空格字符串）非空，会保留。测试如实反映这一点。
     */
    public function test_filter_drops_every_falsy_value(): void
    {
        $data = [
            'a' => '1',
            'b' => '',
            'c' => null,
            'd' => 0,
            'e' => '0',
            'f' => ' ',
            'g' => false,
        ];

        // 剩下 a=1 与 f=' '，拼接原文 'a=1&f= k'
        $this->assertSame('aac6a43b89db079cc1d52635d84a75da', PaymentHelper::aliStyleSign($data, 'k'));
        $this->assertSame('aac6a43b89db079cc1d52635d84a75da', PaymentHelper::aliStyleSign(['a' => '1', 'f' => ' '], 'k'));

        // 关掉过滤：false 变成 '0'，null 仍被 http_build_query 丢掉，签名随之改变
        $this->assertSame('ff18025705bd74966483578dff87adc2', PaymentHelper::aliStyleSign($data, 'k', false));
        $this->assertNotSame(
            PaymentHelper::aliStyleSign($data, 'k', false),
            PaymentHelper::aliStyleSign($data, 'k', true)
        );
    }

    /** 没有任何业务字段时，签名就是 md5(密钥) —— 空报文也能稳定产出 */
    public function test_empty_data_signs_the_key_only(): void
    {
        $this->assertSame('8ce4b16b22b58894aa86c421e8759df3', PaymentHelper::aliStyleSign([], 'k'));
        $this->assertSame(md5('k'), PaymentHelper::aliStyleSign(['sign' => 'whatever'], 'k'));
    }

    /** 输出恒为 32 位小写十六进制：网关回传的 sign_type 一律按 MD5 处理 */
    public function test_signature_is_lowercase_md5(): void
    {
        // 手写拼接原文 money=9.90&out_trade_no=AbCd1234key123 的 md5
        $sign = PaymentHelper::aliStyleSign(['out_trade_no' => 'AbCd1234', 'money' => '9.90'], 'key123');

        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $sign);
        $this->assertSame('c2149e94b03e8585b0d165be65f6a873', $sign);
    }

    /** 值里的 + 与 % 经过 urldecode(http_build_query(...)) 要保持可逆，否则跨网段的中文标题会签不上 */
    public function test_values_survive_the_query_encoding(): void
    {
        $data = ['q' => 'a b+c%d', 'subject' => '订阅 30 天'];

        $this->assertSame(
            PaymentHelper::aliStyleSign(['q' => 'a b+c%d', 'subject' => '订阅 30 天'], 'k'),
            PaymentHelper::aliStyleSign(array_reverse($data, true), 'k', true),
            '顺序不该影响结果'
        );
        $this->assertNotSame(
            PaymentHelper::aliStyleSign($data, 'k'),
            PaymentHelper::aliStyleSign(['q' => 'a+b+c%d'] + $data, 'k'),
            '值不同签名必须不同'
        );
    }

    /**
     * @dataProvider providerVerify
     */
    public function test_verify(array $data, string $sign, bool $expected, string $case): void
    {
        $this->assertSame($expected, PaymentHelper::verify($data, 'testkey', $sign), $case);
    }

    public static function providerVerify(): array
    {
        $data = [
            'out_trade_no' => 'NO123',
            'subject' => 'OtakuCloud',
            'total_amount' => '9.90',
            'sign_type' => 'MD5',
            'sign' => self::EXPECTED_ALIPAY_SIGN,
        ];

        return [
            '正确签名' => [$data, self::EXPECTED_ALIPAY_SIGN, true, ''],
            // 回传报文里的 sign 值本身不参与签名，所以「原样回传」就能自证
            '网关把签名也塞进数组仍然通过' => [$data, self::EXPECTED_ALIPAY_SIGN, true, 'sign/sign_type 被剔除'],
            '错一个字符' => [$data, '55f768fc430ff6608f7070905c855fa5', false, ''],
            '少一位' => [$data, substr(self::EXPECTED_ALIPAY_SIGN, 0, 31), false, 'hash_equals 比长度'],
            '空签名' => [$data, '', false, ''],
            // 大小写敏感的比较：有的网关回传大写签名，那是它自己的展示格式，验签前必须原样比对
            '大写签名判假' => [$data, strtoupper(self::EXPECTED_ALIPAY_SIGN), false, 'hash_equals 是逐字节比较'],
            '另一个密钥的签名' => [$data, self::EXPECTED_ABC_SIGN, false, ''],
            '报文被改过一个字段' => [
                ['out_trade_no' => 'NO124', 'subject' => 'OtakuCloud', 'total_amount' => '9.90'],
                self::EXPECTED_ALIPAY_SIGN,
                false,
                '金额或单号被篡改必须验不过',
            ],
            '金额被改成 0.01' => [
                ['out_trade_no' => 'NO123', 'subject' => 'OtakuCloud', 'total_amount' => '0.01'],
                self::EXPECTED_ALIPAY_SIGN,
                false,
                '',
            ],
        ];
    }

    /** verify 的 $filter 参数要透传到签名侧：某些网关用空串占位，关掉过滤就全假 */
    public function test_verify_passes_the_filter_flag(): void
    {
        $data = ['out_trade_no' => 'NO123', 'empty_field' => ''];
        $signWithoutEmpty = PaymentHelper::aliStyleSign(['out_trade_no' => 'NO123'], 'testkey');

        $this->assertTrue(PaymentHelper::verify($data, 'testkey', $signWithoutEmpty, true));
        $this->assertFalse(PaymentHelper::verify($data, 'testkey', $signWithoutEmpty, false));
    }

    /** 不同密钥必须产出不同签名，否则密钥根本没进拼接尾部 */
    public function test_key_is_appended_to_the_payload(): void
    {
        $data = ['out_trade_no' => 'NO123', 'subject' => 'OtakuCloud', 'total_amount' => '9.90'];

        $this->assertNotSame(PaymentHelper::aliStyleSign($data, 'testkey'), PaymentHelper::aliStyleSign($data, 'otherkey'));
        $this->assertNotSame(PaymentHelper::aliStyleSign($data, ''), PaymentHelper::aliStyleSign($data, 'testkey'));
    }
}
