<?php

namespace Tests\Unit\Clients;

use App\Enums\ProxyType;
use App\Models\Node;
use App\Utils\Clients\Formatters\Clash;
use App\Utils\Clients\Formatters\ClashMeta;
use App\Utils\Clients\Formatters\Stash;
use App\Utils\Clients\NodeCompatibility;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Clash 家族吃不下某个算法值时是「整条丢弃」，而 Clash 更狠：喂进去不支持的值会让整个订阅解析失败
 * （config.go:368 → provider.go:145 → log.Fatalln），所以生成期过滤必须留着。
 *
 * 本类钉两件事：过滤名单与上游实际实现一致（不白丢、也不漏丢），以及丢弃对管理员可见。
 * 判据只有一份 —— NodeCompatibility 调的就是各出口自己的构建器，不另写算法名单。
 */
class NodeCompatibilityTest extends TestCase
{
    use DatabaseTransactions;

    private const UUID = 'd5f2a1b6-7c8e-4f90-9a1b-2c3d4e5f6071';

    private function ssr(array $overrides = []): array
    {
        // 覆盖项必须在前：PHP 的 + 取左侧优先，写反了传入的算法值会被默认值静默吞掉
        return $overrides + [
            'type' => 'shadowsocksr',
            'name' => 'ssr-node',
            'host' => 'ssr.example.com',
            'port' => 40000,
            'passwd' => 'pw',
            'udp' => 1,
            'method' => 'aes-256-cfb',
            'protocol' => 'origin',
            'obfs' => 'plain',
            'obfs_param' => '',
            'protocol_param' => '',
        ];
    }

    private function hysteria2(array $overrides = []): array
    {
        return $overrides + [
            'type' => 'hysteria2',
            'name' => 'h2-node',
            'host' => 'h2.example.com',
            'port' => 8443,
            'passwd' => '12351:pw',
            'udp' => 1,
        ];
    }

    private function vmess(array $overrides = []): array
    {
        return $overrides + [
            'type' => 'vmess',
            'name' => 'vmess-node',
            'host' => 'vmess.example.com',
            'port' => 20000,
            'uuid' => self::UUID,
            'method' => 'auto',
            'udp' => 1,
            'v2_alter_id' => 0,
            'v2_net' => 'ws',
            'v2_host' => '',
            'v2_path' => '/ray',
            'v2_tls' => '',
        ];
    }

    public function test_合规节点没有任何出口缺口(): void
    {
        $this->assertSame([], NodeCompatibility::gaps($this->ssr()));
        $this->assertSame([], NodeCompatibility::gaps($this->vmess()));
        $this->assertSame([], NodeCompatibility::gaps($this->hysteria2()));
    }

    public function test_clash_的_ssr_名单收得下_ctr_系列(): void
    {
        // 上游 Clash v1.18.0 的 streamList 含 aes-128/192/256-ctr（transport/shadowsocks/core/cipher.go:56-58），
        // 名单里没有它等于把可用节点白丢掉
        foreach (['aes-128-ctr', 'aes-192-ctr', 'aes-256-ctr'] as $method) {
            $this->assertIsArray(Clash::buildShadowsocksr($this->ssr(['method' => $method])), 'Clash 支持 '.$method);
            $this->assertSame([], NodeCompatibility::gaps($this->ssr(['method' => $method])), '不应丢弃 method='.$method);
        }
    }

    public function test_meta_的_ss_名单跟上游注册表对齐(): void
    {
        // mihomo v1.19.32 的 SS 支持来自 sing-shadowsocks2 的三组注册表，旧版 Clash 与 Stash 都没有这些
        $ss = ['type' => 'shadowsocks', 'name' => 'ss', 'host' => 'ss.example.com', 'port' => 40000, 'passwd' => 'pw', 'udp' => 1];

        foreach (['ascon128', 'ascon128a', '2022-blake3-chacha8-poly1305', '2022-blake3-aes-128-ccm', '2022-blake3-aes-256-ccm'] as $method) {
            $this->assertIsArray(ClashMeta::buildShadowsocks($ss + ['method' => $method]), 'Meta 支持 '.$method);
            $this->assertSame([Clash::class, Stash::class], array_keys(NodeCompatibility::gaps($ss + ['method' => $method])), $method.' 只该由 Meta 收');
        }

        // SSR 走的是流密码表，mihomo 里 xchacha20 也注册了
        $this->assertIsArray(ClashMeta::buildShadowsocksr($this->ssr(['method' => 'xchacha20'])));
    }

    public function test_auth_chain_c_到_f_被家族三家同时丢弃并指出字段(): void
    {
        // 节点侧实现了（Supported::PROTOCOLS），但 Clash v1.18.0 与 mihomo v1.19.32 都只注册到 auth_chain_b
        foreach (['auth_chain_c', 'auth_chain_d', 'auth_chain_e', 'auth_chain_f'] as $protocol) {
            $gaps = NodeCompatibility::gaps($this->ssr(['protocol' => $protocol]));

            $this->assertSame([Clash::class, ClashMeta::class, Stash::class], array_keys($gaps), $protocol.' 应被三家同时丢弃');
            $this->assertSame('protocol', $gaps[Clash::class], $protocol.' 的缺口字段应指向 protocol');
        }
    }

    public function test_后台可自造的_ssr_算法名同样报得出字段(): void
    {
        // ss_config 的行列后台能自己加（Config/SsConfigController），这些值真实选得出来，而三家的 streamList 都没有
        foreach (['camellia-256-cfb', 'salsa20', 'aes-256-ofb', 'rc4-md5-6'] as $method) {
            $gaps = NodeCompatibility::gaps($this->ssr(['method' => $method]));

            $this->assertCount(3, $gaps, $method.' 应被三家丢弃');
            $this->assertSame('method', $gaps[Clash::class]);
        }
    }

    public function test_名单外的类型不算静默丢弃(): void
    {
        // Clash 的类型名单本来就不含 hysteria2，那是声明式的能力边界，不该报「看不见」
        $this->assertSame([], NodeCompatibility::gaps($this->hysteria2()));
    }

    public function test_stash_按_obfs_丢弃_hysteria2_时指出字段(): void
    {
        $gaps = NodeCompatibility::gaps($this->hysteria2(['obfs' => 'salamander', 'obfs_param' => 'x']));

        $this->assertSame([Stash::class], array_keys($gaps), 'Meta 支持 obfs，Stash 不支持');
        $this->assertSame('obfs', $gaps[Stash::class]);
    }

    public function test_vmess_的_tcp_网络只有_meta_收得下(): void
    {
        $gaps = NodeCompatibility::gaps($this->vmess(['v2_net' => 'tcp']));

        $this->assertSame([Clash::class, Stash::class], array_keys($gaps));
        $this->assertSame('v2_net', $gaps[Clash::class]);
    }

    public function test_ssr_类型一的节点不由节点决定所以不报(): void
    {
        // 订阅里 SSR(type=1) 的 method/protocol/obfs 用用户记录覆盖（ProxyService::generateNodeConfig），
        // 节点自己说的不算，按节点报「不可见」会误导
        $node = $this->node(ProxyType::SHADOWSOCKSR->value, ['method' => 'camellia-256-cfb', 'protocol' => 'auth_chain_c', 'obfs' => 'plain']);

        $this->assertSame([], NodeCompatibility::forNode($node));
    }

    public function test_vnet_节点按自己的_profile_报出缺口(): void
    {
        $node = $this->node(ProxyType::VNET->value, ['method' => 'aes-256-cfb', 'protocol' => 'auth_chain_c', 'obfs' => 'plain']);

        $this->assertSame([Clash::class, ClashMeta::class, Stash::class], array_keys(NodeCompatibility::forNode($node)));
    }

    public function test_ss_节点按自己的_profile_报出缺口(): void
    {
        $node = $this->node(ProxyType::SHADOWSOCKS->value, ['method' => 'camellia-256-cfb']);

        $gaps = NodeCompatibility::forNode($node);

        $this->assertSame([Clash::class, ClashMeta::class, Stash::class], array_keys($gaps));
        $this->assertSame('method', $gaps[Clash::class]);
    }

    public function test_国家行缺失的节点照常报得出缺口(): void
    {
        // country_code 过期时订阅装配会取 $node->country->name 而致命；列表页不能因此打不开
        $node = $this->node(ProxyType::VNET->value, ['method' => 'aes-256-cfb', 'protocol' => 'auth_chain_c', 'obfs' => 'plain'], 'zz');
        $this->assertNull($node->country);

        $this->assertCount(3, NodeCompatibility::forNode($node));
    }

    private function node(int $type, array $profile, string $countryCode = 'us'): Node
    {
        return Node::withoutEvents(fn () => Node::create([
            'name' => 'n-'.$type.'-'.uniqid(),
            'type' => $type,
            'server' => 'n.example.com',
            'port' => 40000,
            'level' => 0,
            'status' => 1,
            'is_display' => 3,
            'is_udp' => 1,
            'country_code' => $countryCode,
            'profile' => $profile,
        ]));
    }
}
