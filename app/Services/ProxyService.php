<?php

namespace App\Services;

use App\Models\Node;
use App\Models\User;
use App\Utils\Clients\Protocols\Text;
use App\Utils\Clients\Protocols\URLSchemes;
use Arr;
use Exception;
use ReflectionClass;
use RuntimeException;

class ProxyService
{
    private static array $servers;

    private ?User $user;

    public function __construct(?User $user = null)
    {
        $this->user = $user ?? auth()->user();
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

    public function getServers(): array
    {
        return self::$servers ?? [];
    }

    public function getNodeList(?int $type = null, bool $isConfig = true): array
    {
        $query = $this->getUser()->nodes()->whereIn('is_display', [2, 3]); // 获取这个账号可用节点

        if ($type === 1) {
            $query->whereIn('type', [1, 4]);
        } elseif ($type !== null) {
            $query->whereType($type);
        }

        $nodes = $query->orderByDesc('node.sort')->orderBy('node.id')->get();

        if ($isConfig) {
            $servers = [];
            foreach ($nodes as $node) {
                $servers[] = $this->getProxyConfig($node);
            }

            return $servers;
        }

        return $nodes;
    }

    private function getUser(): User
    {
        if (! $this->user || ! $this->user->exists) {
            $user = auth()->user();
            if (! $user) {
                throw new RuntimeException('User not authenticated');
            }
            $this->setUser($user);
        }

        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getProxyConfig(Node $node): array
    { // 提取节点信息
        $user = $this->getUser();
        $config = [
            'id' => $node->id,
            'name' => $node->name,
            'country' => $node->country_code,
            'labels' => $node->labels->pluck('name')->toArray(),
            'area' => $node->country->name,
            'host' => $node->host,
            'group' => sysConfig('website_name'),
            'udp' => $node->is_udp,
        ];

        // 如果是中转节点，递归处理父节点配置
        if ($node->relay_node_id) {
            $parent = $this->getProxyConfig($node->relayNode);
            $config += Arr::except($parent, ['id', 'name', 'host', 'group', 'udp']);
            if ($parent['type'] === 'trojan') {
                $config['sni'] = $parent['host'];
            }
            $config['port'] = $node->port;

            return $config;
        }

        // 按节点类型生成特定配置
        return array_merge($config, $this->generateProtocolConfig($node, $user));
    }

    private function generateProtocolConfig(Node $node, User $user): array
    {
        return match ($node->type) {
            0 => [ // Shadowsocks
                'type' => 'shadowsocks',
                'port' => $node->port ?: $user->port,
                'passwd' => $user->passwd,
                ...$node->profile,
            ],
            2 => [ // Vmess
                'type' => 'vmess',
                'port' => $node->port,
                'uuid' => $user->vmess_id,
                ...$node->profile,
            ],
            3 => [ // Trojan
                'type' => 'trojan',
                'port' => $node->port,
                'passwd' => $user->passwd,
                'sni' => '',
                ...$node->profile,
            ],
            default => $this->generateSSRConfig($node, $user), // 1, 4 => SSR
        };
    }

    private function generateSSRConfig(Node $node, User $user): array
    {
        $config = [
            'type' => 'shadowsocksr',
            ...$node->profile,
        ];

        if (! empty($node->profile['passwd']) && $node->port) { // 单端口使用中转的端口
            $config += [
                'port' => $node->port,
                'protocol_param' => "$user->port:$user->passwd",
            ];
        } else {
            $config += [
                'port' => $user->port,
                'protocol_param' => $user->passwd,
            ];

            if ($node->type === 1) {
                $config += [
                    'method' => $user->method,
                    'protocol' => $user->protocol,
                    'obfs' => $user->obfs,
                ];
            }
        }

        return $config;
    }

    public function failedProxyReturn(string $text, ?int $type = 0): void
    {
        $types = ['shadowsocks', 'shadowsocksr', 'vmess', 'trojan'];

        $addition = match ($type) {
            0 => ['method' => 'none', 'passwd' => 'error'],
            1 => ['method' => 'none', 'passwd' => 'error', 'obfs' => 'origin', 'obfs_param' => '', 'protocol' => 'plain', 'protocol_param' => ''],
            2 => ['uuid' => '0', 'v2_alter_id' => 0, 'method' => 'auto'],
            3 => ['passwd' => 'error']
        };

        $this->setServers([['name' => $text, 'type' => $types[$type], 'host' => sysConfig('website_url'), 'port' => 0, 'udp' => 0, ...$addition]]);
    }

    private function setServers(array $servers): void
    {
        self::$servers = $servers;
    }

    private function getClientConfig(string $target): string
    {
        $classes = glob(app_path('Utils/Clients').'/*.php');
        foreach ($classes as $file) {
            $class = 'App\\Utils\\Clients\\'.basename($file, '.php');
            $ref = new ReflectionClass($class);

            $agents = $ref->getConstant('AGENT');
            if (! is_array($agents)) {
                continue;
            }

            foreach ($agents as $agent) {
                if (str_contains($target, $agent)) {
                    return (new $class)->getConfig($this->getServers(), $this->getUser(), $target);
                }
            }
        }

        return URLSchemes::build($this->getServers()); // Default return
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
            default => throw new Exception('Unsupported proxy type: '.$server['type']),
        };
    }
}
