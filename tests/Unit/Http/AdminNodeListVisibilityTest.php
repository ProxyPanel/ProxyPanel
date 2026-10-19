<?php

namespace Tests\Unit\Http;

use App\Enums\ProxyType;
use App\Models\Node;
use App\Utils\Clients\Formatters\Clash;
use App\Utils\Clients\Formatters\ClashMeta;
use App\Utils\Clients\Formatters\Stash;
use App\Utils\Clients\NodeCompatibility;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * 后台节点列表要把「订阅里被客户端整条丢弃」显示出来：算法名单由各出口自己判，
 * 面板放行、用户端却吃不住的算法值（ss_config 与 Supported 都允许）此前在面板上毫无痕迹。
 *
 * 在本进程里渲染整页会额外报一条 risky（输出缓冲没闭合，与后台任何页面同形），断言照常通过。
 */
class AdminNodeListVisibilityTest extends TestCase
{
    use DatabaseTransactions;

    public function test_被丢弃的节点在列表里带说明(): void
    {
        $node = $this->node(['protocol' => 'auth_chain_c']);

        $response = $this->actingAs($this->superAdmin())->get(route('admin.node.index'))->assertStatus(200);

        $this->assertSame(
            [Clash::class, ClashMeta::class, Stash::class],
            array_keys($this->gapsOf($response, $node->name)),
            'auth_chain_c 只有节点侧实现，三家客户端都没注册'
        );

        $tip = trans('admin.node.client_gap', NodeCompatibility::describe($this->gapsOf($response, $node->name)));
        $this->assertStringContainsString($tip, $response->getContent(), '徽标的提示要把客户端与被挡字段说清楚');
        $this->assertStringContainsString('Clash.Meta', $tip);
    }

    public function test_三家都收得下的节点没有缺口(): void
    {
        $node = $this->node(['protocol' => 'auth_chain_a']);

        $response = $this->actingAs($this->superAdmin())->get(route('admin.node.index'))->assertStatus(200);

        $this->assertSame([], $this->gapsOf($response, $node->name));
    }

    private function gapsOf($response, string $name): array
    {
        $row = $response->viewData('nodeList')->firstWhere('name', $name);

        $this->assertNotNull($row, "列表里没有节点 $name");

        return $row->clientGaps;
    }

    private function node(array $overrides): Node
    {
        // 建节点会触发广播与推送任务，这里只要一行可查的节点数据
        return Node::withoutEvents(fn () => Node::create([
            'name' => 'vis-'.uniqid(),
            'type' => ProxyType::VNET->value,
            'server' => 'vis.example.com',
            'port' => 40001,
            'level' => 0,
            'status' => 1,
            'is_display' => 3,
            'is_udp' => 1,
            'country_code' => 'us',
            // 覆盖项在前：PHP 的 + 取左侧优先，写反了 protocol 会被默认值吞掉
            'profile' => $overrides + ['method' => 'aes-256-cfb', 'protocol' => 'origin', 'obfs' => 'plain'],
        ]));
    }
}
