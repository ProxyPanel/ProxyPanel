<?php

namespace Tests\Unit\Http;

use App\Enums\ProxyType;
use App\Http\Requests\Admin\NodeRequest;
use App\Utils\VNet\Supported;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * VNET 节点只能选 VNet-SSR 实际实现的算法名：名单外的值不会让节点下线，
 * 节点是「拒绝建连接、心跳照常上报」，后台看起来一切正常而用户全断。
 *
 * 只取 method/protocol/obfs 三条规则来判，避免被其它 required 字段干扰；
 * 用例不读写任何表（容器要用 sqlite 起：DB_CONNECTION=sqlite DB_DATABASE=":memory:"）。
 */
class VNetAlgorithmWhitelistTest extends TestCase
{
    public function test_vnet_rejects_names_the_backend_does_not_implement(): void
    {
        foreach (['protocol' => 'auth_sha1_v2', 'obfs' => 'random_head', 'method' => 'camellia-128-cfb'] as $field => $value) {
            $this->assertTrue(
                $this->failsField($field, ['type' => ProxyType::VNET->value, $field => $value], $field),
                "VNET 不该接受 {$value}"
            );
        }
    }

    public function test_vnet_accepts_names_the_backend_implements(): void
    {
        $data = [
            'type' => ProxyType::VNET->value,
            'method' => 'aes-256-cfb8',
            'protocol' => 'auth_chain_f',
            'obfs' => 'tls1.2_ticket_fastauth',
        ];

        foreach (['method', 'protocol', 'obfs'] as $field) {
            $this->assertFalse($this->failsField($field, $data, $field), "VNET 应接受 {$data[$field]}");
        }
    }

    public function test_ssr_is_not_constrained_by_the_vnet_list(): void
    {
        $data = ['type' => ProxyType::SHADOWSOCKSR->value, 'protocol' => 'auth_chain_b', 'obfs' => 'http_post', 'method' => 'camellia-128-cfb'];

        $rules = $this->rules($data);

        // type=1 由另一套后端承载，它实现了 auth_chain_b 这些名字，不能跟着 VNET 一起挡
        $this->assertStringNotContainsString('in:', $rules['protocol']);
        $this->assertStringNotContainsString('in:', $rules['obfs']);
        $this->assertStringContainsString('exists:ss_config,name', $rules['protocol']);
    }

    public function test_the_lists_exclude_names_the_backend_does_not_implement(): void
    {
        // 旧四代 auth_sha1 系与 random_head 被上游因漏洞移除，aes-cfb 之外的 legacy 密码 Go 生态没有可信实现
        foreach (['verify_simple', 'auth_simple', 'auth_sha1', 'auth_sha1_v2', 'random_head', 'table'] as $name) {
            $this->assertNotContains($name, array_merge(Supported::PROTOCOLS, Supported::OBFS, Supported::METHODS));
        }

        foreach (['camellia-128-cfb', 'sodium-aes-256-gcm', 'xchacha-ietf-poly1305', 'chacha20-poly1305',
            '2022-blake3-aes-128-gcm', 'idea-cfb', 'seed-cfb', 'rc2-cfb'] as $name) {
            $this->assertNotContains($name, Supported::METHODS);
        }

        // 反面对照：这些是节点确认实现了的，名单不能漏
        foreach (['origin', 'plain', 'aes-256-cfb', 'aes-128-ofb', 'rc4-md5-6', 'chacha20-ietf-poly1305',
            'auth_chain_a', 'auth_chain_f', 'auth_sha1_v4', 'tls1.2_ticket_auth', 'tls1.2_ticket_fastauth', 'http_post'] as $name) {
            $this->assertContains($name, array_merge(Supported::METHODS, Supported::PROTOCOLS, Supported::OBFS));
        }
    }

    /**
     * Supported 是节点仓 docs/capabilities.json 的镜像：同机器上有两个仓检出时逐组比对，
     * 拿不到那份文件（单独检出一个仓）就跳过——同步步骤写在 AGENTS.md 与本文件注释里.
     */
    public function test_the_mirror_matches_the_node_capability_manifest_when_present(): void
    {
        $manifest = dirname(__DIR__, 4).'/VNet-SSR/docs/capabilities.json';
        if (! is_readable($manifest)) {
            $this->markTestSkipped('未找到节点仓的 capabilities.json，跳过镜像对账');
        }

        $expected = json_decode(file_get_contents($manifest), true);
        $this->assertIsArray($expected, '节点仓清单不是合法 JSON');

        foreach (['methods' => Supported::METHODS, 'protocols' => Supported::PROTOCOLS, 'obfs' => Supported::OBFS] as $group => $local) {
            sort($expected[$group]);
            sort($local);
            $this->assertSame($expected[$group], $local, "VNET 的 {$group} 白名单与节点清单不一致");
        }
    }

    private function rules(array $data): array
    {
        return NodeRequest::create('/admin/node', 'POST', $data)->rules();
    }

    private function failsField(string $field, array $data, string $rule): bool
    {
        $rules = array_intersect_key($this->rules($data), array_flip([$field]));

        return Validator::make($data, $rules)->fails();
    }
}
