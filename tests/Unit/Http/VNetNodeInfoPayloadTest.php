<?php

namespace Tests\Unit\Http;

use App\Enums\ProxyType;
use App\Models\Node;
use App\Models\NodeAuth;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * `GET {prefix}/node/{node}` 的载荷形状：节点侧 `model.NodeInfo` 用严格 json.Unmarshal 绑定，
 * 少一个键不会报错，只会让那个字段静默退回零值 —— 而面板早已把同一个值发进订阅，
 * 于是客户端与节点按不同的参数跑（protocol_param 就是这样：节点恒为空，auth_chain_f 的
 * 包长表轮换秒数两侧不一致等于首连即失配）。所以这里按节点绑定的字段全集判，不按「有没有报错」判。
 *
 * 字段全集的来源是 VNet-SSR 仓 docs/openapi.yaml 的 NodeInfo schema 与 model/model.go。
 * 节点不入库（内存里造 + 直接给 auth 关系），但容器仍要能连库才起得来。
 */
class VNetNodeInfoPayloadTest extends TestCase
{
    use DatabaseTransactions;

    /** VNet-SSR 的 NodeInfo 会去绑的键，一个都不能少 */
    private const BOUND_KEYS = [
        'client_limit', 'id', 'is_udp', 'method', 'obfs', 'obfs_param', 'passwd', 'port',
        'protocol', 'protocol_param', 'push_port', 'redirect_url', 'secret', 'single', 'speed_limit',
    ];

    private function node(array $profile = []): Node
    {
        $node = new Node([
            'name' => 'nodeinfo-'.uniqid(),
            'type' => ProxyType::VNET->value,
            'server' => '127.0.0.1',
            'ip' => '127.0.0.1',
            'push_port' => 443,
            'client_limit' => 30,
            'speed_limit' => 0,
            'profile' => $profile,
            'details' => [],
        ]);
        $node->id = 1;
        $node->exists = true;
        $node->setRelation('auth', (new NodeAuth)->newFromBuilder(['secret' => 'test-secret']));

        return $node;
    }

    public function test_node_info_carries_every_field_the_node_binds(): void
    {
        $keys = array_keys($this->node()->getSSRConfig());
        sort($keys);

        $this->assertSame(self::BOUND_KEYS, $keys);
    }

    public function test_protocol_param_comes_from_the_profile(): void
    {
        // 前段是并发上限、# 后段只有 auth_chain_f 用作包长表轮换秒数；管理员填了什么节点就该按什么跑
        $config = $this->node(['protocol' => 'auth_chain_f', 'protocol_param' => '0#3600'])->getSSRConfig();

        $this->assertSame('0#3600', $config['protocol_param']);
    }

    public function test_missing_protocol_param_is_an_empty_string_not_null(): void
    {
        $config = $this->node(['protocol' => 'origin'])->getSSRConfig();

        // 与 method/obfs_param 同形：没填就是空串，节点按「取不到整数→不改动上限」处理
        $this->assertSame('', $config['protocol_param']);
    }
}
