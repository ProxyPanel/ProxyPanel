<?php

namespace Tests\Unit\Services;

use App\Enums\ProxyType;
use App\Models\Node;
use App\Models\NodeAuth;
use App\Services\NodeService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * 节点服务：类型展示按承载协议归组，且模型读出的 type 恒为 int。
 */
class NodeServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_active_types_fold_vnet_into_ssr(): void
    {
        $this->nodes([ProxyType::VNET->value, ProxyType::HYSTERIA2->value, ProxyType::SHADOWSOCKS->value, 9]);

        $types = (new NodeService)->getActiveNodeTypes();

        $this->assertSame([1, 5, 0], array_keys($types), 'VNET 不单独成项，越界值不进列表');
        $this->assertSame(['ShadowsocksR', 'Hysteria2', 'Shadowsocks'], array_values($types));
    }

    public function test_deployment_config_falls_back_for_unknown_and_ss_types(): void
    {
        $service = new NodeService;

        foreach ([ProxyType::SHADOWSOCKS->value, 9] as $type) {
            $config = $service->getNodeDeploymentConfig($this->node($type));

            $this->assertSame([['name' => trans('common.none')]], $config, "type {$type} 走默认分支");
        }
    }

    public function test_trojan_without_host_returns_the_domain_hint(): void
    {
        // getTrojanConfig 的 $webApi 是 string 形参，站点地址没配时 sysConfig 给 null 会直接抛 TypeError，
        // 这里显式填上，用例不依赖开发库里的 config 行
        config(['settings.web_api_url' => 'https://probe.test']);

        $config = (new NodeService)->getNodeDeploymentConfig($this->node(ProxyType::TROJAN->value));

        $this->assertStringContainsString('href="'.route('admin.node.edit', 1).'"', $config['hint']);
    }

    public function test_hysteria2_without_domain_returns_the_domain_hint(): void
    {
        config([
            'settings.web_api_url' => 'https://probe.test',
            'settings.webmaster_email' => 'admin@probe.test',
        ]);

        // 没填域名时 host 会退回 IP，而 ACME 只认域名：那种配置申请不到证书，服务起不来
        $config = (new NodeService)->getNodeDeploymentConfig($this->hysteria2Node());

        $this->assertStringContainsString('href="'.route('admin.node.edit', 1).'"', $config['hint']);
    }

    public function test_hysteria2_without_admin_email_returns_the_email_hint(): void
    {
        config([
            'settings.web_api_url' => 'https://probe.test',
            'settings.webmaster_email' => '',
        ]);

        $config = (new NodeService)->getNodeDeploymentConfig($this->hysteria2Node('node.example.test'));

        $this->assertStringContainsString('href="'.route('admin.system.index').'"', $config['hint']);
    }

    public function test_hysteria2_config_carries_the_domain_and_the_admin_email(): void
    {
        config([
            'settings.web_api_url' => 'https://probe.test',
            'settings.webmaster_email' => 'admin@probe.test',
        ]);

        $node = $this->hysteria2Node('node.example.test');
        $node->setRelation('auth', new NodeAuth(['key' => 'node-key', 'secret' => 'node-secret']));

        $install = (new NodeService)->getNodeDeploymentConfig($node)[0]['commands']['install'];

        $this->assertStringContainsString('- node.example.test', $install);
        $this->assertStringContainsString('email: admin@probe.test', $install);
    }

    public function test_type_is_int_even_when_it_arrives_as_a_request_string(): void
    {
        $node = Node::withoutEvents(fn () => Node::create(['name' => 'cast', 'type' => '4', 'profile' => []]));

        $this->assertSame(4, $node->type);
        $this->assertSame(4, $node->fresh()->type);
        $this->assertSame('VNET', $node->fresh()->type_label);
    }

    public function test_protocol_config_is_derived_from_the_enum(): void
    {
        $this->assertSame(ProxyType::protocols(), config('common.proxy_protocols'));
    }

    public function test_vnet_install_command_fetches_this_forks_own_script(): void
    {
        // getVnetConfig 的 $webApi 是 string 形参，站点地址没配时 sysConfig 给 null 会抛 TypeError
        config(['settings.web_api_url' => 'https://probe.test']);

        $node = (new Node)->newFromBuilder(['id' => 7, 'type' => ProxyType::VNET->value, 'profile' => []]);
        $node->setRelation('auth', new NodeAuth(['key' => 'node-key']));

        $commands = (new NodeService)->getNodeDeploymentConfig($node)[0]['commands'];

        // 短链改指向没人能发现，而装错包的代价是整条 VNET 线路不可用（Release 里那份是 2020 年的上游包）
        $this->assertStringContainsString('raw.githubusercontent.com/ProxyPanel/VNet-SSR/master/release/deploy.sh', $commands['install']);
        $this->assertStringNotContainsString('bit.ly', $commands['install']);
        $this->assertStringNotContainsString('bit.ly', $commands['uninstall']);
        $this->assertStringContainsString('WEB_API="https://probe.test"', $commands['install']);
        $this->assertStringContainsString('NODE_ID=7', $commands['install']);
        $this->assertStringContainsString('NODE_KEY=node-key', $commands['install']);
        // 调优跟着装机走，运维复制粘贴就完事；卸载那条不该顺带把机器配置退掉
        $this->assertStringContainsString('WITH_TUNE=1', $commands['install']);
        $this->assertStringNotContainsString('WITH_TUNE', $commands['uninstall']);
        // 这条同时是「重置密钥后重新下发凭据 / 修坏安装」的那条：不带 -f 时脚本在版本相同处早退，key 进不了 /etc/vnet/config.json
        $this->assertStringContainsString('bash -s -- -f', $commands['install']);
    }

    public function test_vnet_update_command_swaps_only_the_binary(): void
    {
        config(['settings.web_api_url' => 'https://probe.test']);

        $node = (new Node)->newFromBuilder(['id' => 7, 'type' => ProxyType::VNET->value, 'profile' => []]);
        $node->setRelation('auth', new NodeAuth(['key' => 'node-key']));

        $update = (new NodeService)->getNodeDeploymentConfig($node)[0]['commands']['update'];

        // 脚本必须是本 fork 自己的（同 install 那条判据）
        $this->assertStringContainsString('raw.githubusercontent.com/ProxyPanel/VNet-SSR/master/release/upgrade.sh', $update);
        // 升级不重写 /etc/vnet/config.json，命令里出现 key 就说明拿错了脚本：弹窗命令是明文展示给每个能看授权页的人的
        $this->assertStringNotContainsString('NODE_KEY', $update);
        $this->assertStringNotContainsString('NODE_ID', $update);
        $this->assertStringNotContainsString('WEB_API', $update);
        $this->assertStringNotContainsString("\n", $update, '一行一条命令，带续行会塌进同一行');
    }

    private function node(int $type): Node
    {
        return (new Node)->newFromBuilder(['id' => 1, 'type' => $type, 'port' => 443, 'profile' => []]);
    }

    private function hysteria2Node(?string $server = null): Node
    {
        return (new Node)->newFromBuilder([
            'id' => 1,
            'type' => ProxyType::HYSTERIA2->value,
            'port' => 443,
            'push_port' => 8443,
            'server' => $server,
            'profile' => [],
        ]);
    }

    private function nodes(array $types): void
    {
        foreach ($types as $type) {
            Node::withoutEvents(fn () => Node::create(['name' => 'n-'.$type, 'type' => $type, 'profile' => []]));
        }
    }
}
