<?php

namespace App\Services;

use App\Models\Node;
use App\Models\User;
use App\Utils\Clients\Protocols\Text;
use App\Utils\Clients\Protocols\URLSchemes;
use Arr;
use ReflectionClass;

class ProxyService
{
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

    public function getProxyText(string $target, ?int $type = null): string
    {
        if (empty($this->getServers())) {
            $servers = $this->getNodeList($type);
            if (empty($servers)) {
                $this->failedProxyReturn(trans('errors.subscribe.none'), $type);
            } else {
                if (sysConfig('rand_subscribe')) {// 打乱数组
                    $servers = Arr::shuffle($servers);
                }

                $max = (int) sysConfig('subscribe_max');
                if ($max && count($servers) > $max) { // 订阅数量限制
                    $servers = Arr::random($servers, $max);
                }

                $this->setServers($servers);
            }
        }

        return $this->getClientConfig($target);
    }

    public function getServers(): ?array
    {
        return self::$servers ?? null;
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
                        'type' => 'vmess',
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

    public function failedProxyReturn(string $text, ?int $type = 0): void
    {
        $url = sysConfig('website_url');

        $data = [
            'name' => $text,
            'type' => [0 => 'shadowsocks', 1 => 'shadowsocksr', 2 => 'vmess', 3 => 'trojan'][$type],
            'host' => $url,
            'port' => 0,
            'udp' => 0,
        ];

        $addition = match ($type) {
            0 => ['method' => 'none', 'passwd' => 'error'],
            1 => ['method' => 'none', 'passwd' => 'error', 'obfs' => 'origin', 'obfs_param' => '', 'protocol' => 'plain', 'protocol_param' => ''],
            2 => ['uuid' => '0', 'v2_alter_id' => 0, 'method' => 'auto'],
            3 => ['passwd' => 'error']
        };

        $this->setServers([array_merge($data, $addition)]);
    }

    private function setServers(array $servers): void
    {
        self::$servers = $servers;
    }

    private function getClientConfig(string $target): string
    {
        foreach (glob(app_path('Utils/Clients').'/*.php') as $file) {
            $class = 'App\\Utils\\Clients\\'.basename($file, '.php');
            $reflectionClass = new ReflectionClass($class);

            foreach ($reflectionClass->getConstant('AGENT') as $agent) {
                if (str_contains($target, $agent)) {
                    return (new $class)->getConfig($this->getServers(), $this->getUser(), $target);
                }
            }
        }

        return URLSchemes::build($this->getServers()); // Origin
    }

    public function getUser(): ?User
    {
        return self::$user;
    }

    public function getProxyCode(string $target, ?int $type = null): ?string
    {// 客户端用代理信息
        $servers = $this->getNodeList($type);
        if (empty($servers)) {
            return null;
        }

        $this->setServers($servers);

        return $this->getClientConfig($target);
    }

    public function getUserProxyConfig(array $server, bool $is_url): string
    { // 用户显示用代理信息
        $type = $is_url ? new URLSchemes : new Text;

        return match ($server['type']) {
            'shadowsocks' => $type->buildShadowsocks($server),
            'shadowsocksr' => $type->buildShadowsocksr($server),
            'vmess' => $type->buildVmess($server),
            'trojan' => $type->buildTrojan($server),
        };
    }
}
