<?php

namespace App\Services;

use App\Enums\ProxyType;
use App\Models\Node;
use Illuminate\Database\Eloquent\Builder;

class NodeService
{
    public function getActiveNodeTypes(?Builder $nodes = null): array
    {
        $result = [];
        // 按承载协议归组：VNET(4) 与 SSR(1) 出一张牌
        foreach (($nodes ?? Node::whereStatus(1))->pluck('type') as $type) {
            $carrier = ProxyType::tryFrom((int) $type)?->carrier();

            if ($carrier) {
                $result[$carrier->value] = $carrier->label();
            }
        }

        return $result;
    }

    public function getNodeDeploymentConfig(Node $node): array
    {
        $webApi = sysConfig('web_api_url') ?: sysConfig('website_url');

        return match (ProxyType::tryFrom($node->type)) {
            ProxyType::SHADOWSOCKSR, ProxyType::VNET => $this->getVnetConfig($node, $webApi),
            ProxyType::V2RAY => $this->getV2RayConfig($node, $webApi),
            ProxyType::TROJAN => $this->getTrojanConfig($node, $webApi),
            ProxyType::HYSTERIA2 => $this->getHysteria2Config($node),
            default => $this->getDefaultConfig(),
        };
    }

    private function getVnetConfig(Node $node, string $webApi): array
    {
        // 装的是本仓自己的脚本（bit.ly 短链会 301 到它，但短链改指向时没人能发现）；
        // 脚本再从 release/version.json 取要装的版本，所以升版本要同时动 version.json 和 tag
        $script = 'https://raw.githubusercontent.com/ProxyPanel/VNet-SSR/master/release/deploy.sh';
        $upgradeScript = 'https://raw.githubusercontent.com/ProxyPanel/VNet-SSR/master/release/upgrade.sh';

        return [[
            'name' => 'VNET',
            'commands' => [
                // WITH_TUNE=1 是让「复制粘贴就完事」成立：调优跟着装机走，不要求运维改命令；不想调优就删这一个变量
                // -f 让这条同时是「重新下发凭据 / 修坏安装」那条：不带它时 deploy.sh 在版本相同处早退，/etc/vnet/config.json 不会被重写
                'install' => "(yum install curl 2> /dev/null || apt install curl 2> /dev/null) \\\n&& curl -L -s {$script} \\\n| WEB_API=\"$webApi\" \\\nNODE_ID=$node->id \\\nNODE_KEY={$node->auth->key} \\\nWITH_TUNE=1 \\\nbash -s -- -f",
                // 日常版本升级走这条：只换二进制，不碰配置/单元/调优，所以命令里不需要也不应出现凭据；坏包起不来会自动回滚
                'update' => "curl -L -s {$upgradeScript} | bash",
                'uninstall' => "curl -L -s {$script} | bash -s -- --remove",
                'start' => 'systemctl start vnet',
                'stop' => 'systemctl stop vnet',
                'restart' => 'systemctl restart vnet',
                'status' => 'systemctl status vnet',
                'recent_logs' => 'journalctl -x -n 300 --no-pager -u vnet',
                'real_time_logs' => 'journalctl -u vnet -f',
            ],
        ]];
    }

    private function getV2RayConfig(Node $node, string $webApi): array
    {
        return [
            [
                'name' => 'VNET-V2Ray',
                'commands' => [
                    'install' => "(yum install curl 2> /dev/null || apt install curl 2> /dev/null) \\\n&& curl -L -s https://bit.ly/3oO3HZy \\\n| WEB_API=\"$webApi\" \\\nNODE_ID=$node->id \\\nNODE_KEY={$node->auth->key} \\\nbash",
                    'update' => trans('admin.node.auth.deploy.same'),
                    'uninstall' => 'curl -L -s https://bit.ly/3oO3HZy | bash -s -- --remove',
                    'start' => 'systemctl start vnet-v2ray',
                    'stop' => 'systemctl stop vnet-v2ray',
                    'status' => 'systemctl status vnet-v2ray',
                    'recent_logs' => 'journalctl -x -n 300 --no-pager -u vnet-v2ray',
                    'real_time_logs' => 'journalctl -u vnet-v2ray -f',
                ],
            ],
            [
                'name' => 'V2Ray-Poseidon',
                'commands' => [
                    'install' => "(yum install curl 2> /dev/null || apt install curl 2> /dev/null) \\\n&& curl -L -s https://bit.ly/2HswWko \\\n| WEB_API=\"$webApi\" \\\nNODE_ID=$node->id \\\nNODE_KEY={$node->auth->key} \\\nbash",
                    'update' => 'curl -L -s https://bit.ly/2HswWko | bash',
                    'uninstall' => 'curl -L -s https://mrw.so/5IHPR4 | bash',
                    'start' => 'systemctl start v2ray',
                    'stop' => 'systemctl stop v2ray',
                    'status' => 'systemctl status v2ray',
                    'recent_logs' => 'journalctl -x -n 300 --no-pager -u v2ray',
                    'real_time_logs' => 'journalctl -u v2ray -f',
                ],
            ],
        ];
    }

    private function getTrojanConfig(Node $node, string $webApi): array
    {
        if (empty($node->host)) {
            return [
                'hint' => trans('admin.node.auth.deploy.host_hint', ['url' => route('admin.node.edit', $node->id)]),
            ];
        }

        return [[
            'name' => 'Trojan-Poseidon',
            'commands' => [
                'install' => "(yum install curl 2> /dev/null || apt install curl 2> /dev/null) \\\n&& curl -L -s https://mrw.so/6cMfGy \\\n| WEB_API=\"$webApi\" \\\nNODE_ID=$node->id \\\nNODE_KEY={$node->auth->key} \\\nNODE_HOST=$node->host \\\nbash",
                'update' => 'curl -L -s https://mrw.so/6cMfGy | bash',
                'uninstall' => 'curl -L -s https://mrw.so/5ulpvu | bash',
                'start' => 'systemctl start trojanp',
                'stop' => 'systemctl stop trojanp',
                'status' => 'systemctl status trojanp',
                'recent_logs' => 'journalctl -x -n 300 --no-pager -u trojanp',
                'real_time_logs' => 'journalctl -u trojanp -f',
            ],
        ]];
    }

    private function getHysteria2Config(Node $node): array
    {
        // ACME 只认域名：host 会在没填域名时退回 IP，那种配置申请不到证书，hysteria-server 起不来
        if (empty($node->server)) {
            return [
                'hint' => trans('admin.node.auth.deploy.host_hint', ['url' => route('admin.node.edit', $node->id)]),
            ];
        }

        // 邮箱缺失会生成 "email: " 空值，证书申请当场失败
        if (empty(sysConfig('webmaster_email'))) {
            return [
                'hint' => trans('admin.node.auth.deploy.email_hint', ['url' => route('admin.system.index')]),
            ];
        }

        $configContent = "listen: :$node->port\n";

        $configContent .= "acme:\n  domains:\n    - $node->host\n  email: ".sysConfig('webmaster_email')."\n";

        $configContent .= "auth:\n  type: http\n  http:\n    url: ".route('api.hysteria2.auth', $node->id)."\n    insecure: ".($node->allow_insecure ? 'true' : 'false')."\n";

        $configContent .= "masquerade:\n  type: proxy\n  proxy:\n    url: https://bing.com\n    rewriteHost: true\n";

        // 添加带宽配置
        if ($node->upload_mbps > 0 || $node->download_mbps > 0) {
            $configContent .= "bandwidth:\n";
            if ($node->upload_mbps > 0) {
                $configContent .= "   up: $node->upload_mbps mbps\n";
            }
            if ($node->download_mbps > 0) {
                $configContent .= "   down: $node->download_mbps mbps\n";
            }
        }

        $configContent .= 'ignoreClientBandwidth: '.($node->ignore_client_bandwidth ? 'true' : 'false')."\ntrafficStats:\n  listen: :$node->push_port\n  secret: {$node->auth->secret}\n";

        return [[
            'name' => 'Hysteria2 Official',
            'commands' => [
                'install' => "(yum install curl 2> /dev/null || apt install curl 2> /dev/null) \\\n&& bash <(curl -fsSL https://get.hy2.sh/) \\\n&& mkdir -p /etc/hysteria \\\n&& cat > /etc/hysteria/config.yaml << EOF\n{$configContent}EOF",
                'update' => 'bash <(curl -fsSL https://get.hy2.sh/)',
                'uninstall' => 'bash <(curl -fsSL https://get.hy2.sh/) --remove',
                'start' => 'systemctl start hysteria-server.service',
                'stop' => 'systemctl stop hysteria-server.service',
                'status' => 'systemctl status hysteria-server.service',
                'recent_logs' => 'journalctl -x -n 300 --no-pager -u hysteria-server.service',
                'real_time_logs' => 'journalctl -u hysteria-server.service -f',
            ],
        ]];
    }

    private function getDefaultConfig(): array
    {
        return [[
            'name' => trans('common.none'),
        ]];
    }
}
