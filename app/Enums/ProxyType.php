<?php

namespace App\Enums;

/**
 * 代理协议类型，case 值即 `node.type` 的存储值。
 */
enum ProxyType: int
{
    case SHADOWSOCKS = 0;
    case SHADOWSOCKSR = 1;
    case V2RAY = 2;
    case TROJAN = 3;
    case VNET = 4;
    case HYSTERIA2 = 5;

    /** 协议名，`config/common.proxy_protocols` 由它派生 */
    public function label(): string
    {
        return match ($this) {
            self::SHADOWSOCKS => 'Shadowsocks',
            self::SHADOWSOCKSR => 'ShadowsocksR',
            self::V2RAY => 'V2Ray',
            self::TROJAN => 'Trojan',
            self::VNET => 'VNET',
            self::HYSTERIA2 => 'Hysteria2',
        };
    }

    /** VNET 由 SSR 那一套字段承载，比较、筛选、出配置前都先归到承载协议 */
    public function carrier(): self
    {
        return match ($this) {
            self::VNET => self::SHADOWSOCKSR,
            default => $this,
        };
    }

    /** 订阅 payload 里 server['type'] 的 token，与 `app/Utils/Clients/Formatters` 的白名单同一套 */
    public function clientType(): string
    {
        return match ($this->carrier()) {
            self::SHADOWSOCKS => 'shadowsocks',
            self::SHADOWSOCKSR => 'shadowsocksr',
            self::V2RAY => 'vmess',
            self::TROJAN => 'trojan',
            self::HYSTERIA2 => 'hysteria2',
        };
    }

    /**
     * @return array<int, string> 顺序数组 `[type 值 => 协议名]`
     */
    public static function protocols(): array
    {
        $protocols = [];

        foreach (self::cases() as $case) {
            $protocols[$case->value] = $case->label();
        }

        return $protocols;
    }

    /**
     * @return array<int, int> 全部合法存储值
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * 归到同一承载协议的一组存储值，SSR 与 VNET 同为一组.
     *
     * @return array<int, int>
     */
    public static function carriedValues(self $carrier): array
    {
        return array_map(
            static fn (self $case): int => $case->value,
            array_filter(self::cases(), static fn (self $case): bool => $case->carrier() === $carrier),
        );
    }
}
