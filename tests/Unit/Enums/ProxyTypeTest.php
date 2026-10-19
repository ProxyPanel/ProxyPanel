<?php

namespace Tests\Unit\Enums;

use App\Enums\ProxyType;
use PHPUnit\Framework\TestCase;

/**
 * 协议类型枚举：值、承载关系与客户端 token 都要与历史编码逐一对齐。
 */
class ProxyTypeTest extends TestCase
{
    public function test_values_match_the_historical_protocol_numbers(): void
    {
        $this->assertSame([0, 1, 2, 3, 4, 5], ProxyType::values());
        $this->assertSame(
            [
                0 => 'Shadowsocks',
                1 => 'ShadowsocksR',
                2 => 'V2Ray',
                3 => 'Trojan',
                4 => 'VNET',
                5 => 'Hysteria2',
            ],
            ProxyType::protocols(),
        );
    }

    public function test_vnet_is_carried_by_ssr_and_others_carry_themselves(): void
    {
        $this->assertSame(ProxyType::SHADOWSOCKSR, ProxyType::VNET->carrier());

        foreach ([ProxyType::SHADOWSOCKS, ProxyType::SHADOWSOCKSR, ProxyType::V2RAY, ProxyType::TROJAN, ProxyType::HYSTERIA2] as $case) {
            $this->assertSame($case, $case->carrier());
        }

        $this->assertSame([1, 4], array_values(ProxyType::carriedValues(ProxyType::SHADOWSOCKSR)));
        $this->assertSame([0], array_values(ProxyType::carriedValues(ProxyType::SHADOWSOCKS)));
    }

    /**
     * @dataProvider clientTypeProvider
     */
    public function test_client_type_token(ProxyType $type, string $expected): void
    {
        $this->assertSame($expected, $type->clientType());
    }

    public static function clientTypeProvider(): array
    {
        return [
            [ProxyType::SHADOWSOCKS, 'shadowsocks'],
            [ProxyType::SHADOWSOCKSR, 'shadowsocksr'],
            [ProxyType::VNET, 'shadowsocksr'],
            [ProxyType::V2RAY, 'vmess'],
            [ProxyType::TROJAN, 'trojan'],
            [ProxyType::HYSTERIA2, 'hysteria2'],
        ];
    }
}
