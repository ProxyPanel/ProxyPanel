<?php

namespace App\Services;

use App\Helpers\ClientConfig;
use App\Models\Node;
use App\Models\User;
use App\Utils\Clients\Text;
use App\Utils\Clients\URLSchemes;
use Arr;

class ProxyService
{
    use ClientConfig;

    private static ?User $user;

    private static array $servers;

    public function __construct(?User $user = null)
    {
        $this->setUser($user ?? auth()->user());
    }

    public function setUser(?User $user = null): void
    {
        self::$user = $user;
    }

    public function getUser(): ?User
    {
        return self::$user;
    }

    public function getServers(): array
    {
        return self::$servers;
    }

    public function getProxyText(string $target, ?int $type = null): string
    {
        $servers = $this->getNodeList($type);
        if (empty($servers)) {
            return $this->failedProxyReturn(trans('errors.subscribe.none'), $type);
        }

        if (sysConfig('rand_subscribe')) {// 打乱数组
            $servers = Arr::shuffle($servers);
        }

        $max = (int) sysConfig('subscribe_max');
        if ($max && count($servers) > $max) { // 订阅数量限制
            $servers = Arr::random($servers, $max);
        }

        $this->setServers($servers);

        return $this->clientConfig($target);
    }

    public function getNodeList(?int $type = null, bool $isConfig = true): array
    {
        $query = self::$user->nodes()->whereIn('is_display', [2, 3]); // 获取这个账号可用节点

        if ($type) {
            if ($type === 1) {
                $query = $query->whereIn('type', [1, 4]);
            } else {
                $query = $query->whereType($type);
            }
        }

        $nodes = $query->orderByDesc('sort')->orderBy('id')->get();

        if ($isConfig) {
            $servers = [];
            foreach ($nodes as $node) {
                $servers[] = $this->getProxyConfig($node);
            }

            return $servers;
        }

        return $nodes;
    }

    public function getProxyConfig(Node $node): array
    { // 提取节点信息
        $user = self::$user;
        $config = [
            'id' => $node->id,
            'name' => $node->name,
            'area' => $node->country->name,
            'host' => $node->host,
            'group' => sysConfig('website_name'),
            'udp' => $node->is_udp,
        ];

        if ($node->relay_node_id) {
            $parentConfig = $this->getProxyConfig($node->relayNode);
            $config = array_merge($config, Arr::except($parentConfig, ['id', 'name', 'host', 'group', 'udp']));
            if ($parentConfig['type'] === 'trojan') {
                $config['sni'] = $parentConfig['host'];
            }
            $config['port'] = $node->port;
        } else {
            switch ($node->type) {
                case 0:
                    $config = array_merge($config, [
                        'type' => 'shadowsocks',
                        'passwd' => $user->passwd,
                    ], $node->profile);
                    if ($node->port && $node->port !== 0) {
                        $config['port'] = $node->port;
                    } else {
                        $config['port'] = $user->port;
                    }
                    break;
                case 2:
                    $config = array_merge($config, [
                        'type' => 'v2ray',
                        'port' => $node->port,
                        'uuid' => $user->vmess_id,
                    ], $node->profile);
                    break;
                case 3:
                    $config = array_merge($config, [
                        'type' => 'trojan',
                        'port' => $node->port,
                        'passwd' => $user->passwd,
                        'sni' => '',
                    ], $node->profile);
                    break;
                case 1:
                case 4:
                default:
                    $config = array_merge($config, ['type' => 'shadowsocksr'], $node->profile);
                    if ($node->profile['passwd'] && $node->port) {
                        //单端口使用中转的端口
                        $config['port'] = $node->port;
                        $config['protocol_param'] = $user->port.':'.$user->passwd;
                    } else {
                        $config['port'] = $user->port;
                        $config['passwd'] = $user->passwd;
                        if ($node->type === 1) {
                            $config['method'] = $user->method;
                            $config['protocol'] = $user->protocol;
                            $config['obfs'] = $user->obfs;
                        }
                    }
                    break;
            }
        }

        return $config;
    }

    public function failedProxyReturn(string $text, ?int $type = 1): string
    {
        $url = sysConfig('website_url');

        return match ($type) {
            1 => 'vmess://'.base64url_encode(json_encode(['v' => '2', 'ps' => $text, 'add' => $url, 'port' => 0, 'id' => 0, 'aid' => 0, 'net' => 'tcp', 'type' => 'none', 'host' => $url, 'path' => '/', 'tls' => 'tls'], JSON_PRETTY_PRINT)),
            2 => 'trojan://0@0.0.0.0:0?peer=0.0.0.0#'.rawurlencode($text),
            default => 'ssr://'.base64url_encode('0.0.0.0:0:origin:none:plain:MDAwMA/?obfsparam=&protoparam=&remarks='.base64url_encode($text).'&group='.base64url_encode(sysConfig('website_name')).'&udpport=0&uot=0'),
        }.PHP_EOL;
    }

    private function setServers(array $servers): void
    {
        self::$servers = $servers;
    }

    public function getProxyCode(string $target, ?int $type = null): ?string
    {// 客户端用代理信息
        $servers = $this->getNodeList($type);
        if (empty($servers)) {
            return null;
        }

        $this->setServers($servers);

        return $this->clientConfig($target);
    }

    public function getUserProxyConfig(array $server, bool $is_url): string
    { // 用户显示用代理信息
        $type = $is_url ? new URLSchemes() : new Text();

        return match ($server['type']) {
            'shadowsocks' => $type->buildShadowsocks($server),
            'shadowsocksr' => $type->buildShadowsocksr($server),
            'v2ray' => $type->buildVmess($server),
            'trojan' => $type->buildTrojan($server),
        };
    }
}
