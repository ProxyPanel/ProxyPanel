<?php

namespace App\Services;

use App\Models\Node;
use Illuminate\Database\Eloquent\Builder;

class NodeService
{
    public function getActiveNodeTypes(?Builder $nodes = null): array
    {
        // / 1. 获取 Node 存在的 type 集合并去重
        $nodeTypes = ($nodes ?? Node::whereStatus(1))->pluck('type')->unique()->toArray();
        $protocols = config('common.proxy_protocols', []);

        $result = [];
        foreach ($nodeTypes as $type) {
            if (! isset($protocols[$type])) {
                continue;
            }

            // 特殊逻辑：type 4 映射到 key 1，其余保持不变
            $key = ($type === 4) ? 1 : $type;
            $result[$key] = $protocols[$key];
        }

        return $result;
    }

    public function getNodeDeploymentConfig(Node $node): array
    {
        $webApi = sysConfig('web_api_url') ?: sysConfig('website_url');

        return match ($node->type) {
            1,4 => $this->getVnetConfig($node, $webApi),
            2 => $this->getV2RayConfig($node, $webApi),
            3 => $this->getTrojanConfig($node, $webApi),
            5 => $this->getHysteria2Config($node),
            default => $this->getDefaultConfig(),
        };
    }

    private function getVnetConfig(Node $node, string $webApi): array
    {
        return [[
            'name' => 'VNET',
            'commands' => [
                'install' => "(yum install curl 2> /dev/null || apt install curl 2> /dev/null) \\\n&& curl -L -s https://bit.ly/3828OP1 \\\n| WEB_API=\"$webApi\" \\\nNODE_ID=$node->id \\\nNODE_KEY={$node->auth->key} \\\nbash",
                'update' => trans('admin.node.auth.deploy.same'),
                'uninstall' => 'curl -L -s https://bit.ly/3828OP1 | bash -s -- --remove',
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
                'requires_host' => true,
                'edit_url' => route('admin.node.edit', $node->id),
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
        // 生成配置文件内容
        $configContent = "listen: :$node->port\n";

        if (! empty($node->host)) {
            $configContent .= "acme:\n  domains:\n    - $node->host\n  email: ".sysConfig('webmaster_email')."\n";
        }

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
