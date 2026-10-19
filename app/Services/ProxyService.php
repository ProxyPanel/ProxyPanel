<?php

namespace App\Services;

use App\Enums\ProxyType;
use App\Models\Node;
use App\Models\User;
use App\Utils\Clients\Formatters\Text;
use App\Utils\Clients\Formatters\URLSchemes;
use Arr;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use ReflectionClass;

/**
 * 订阅代理服务
 * 提供代理配置的生成、管理和格式化功能.
 */
class ProxyService
{
    /**
     * 节点配置缓存，键为订阅类型（未指定类型时为 all）。
     *
     * 必须是实例属性且按类型分键：static 单槽缓存会在同一进程内换用户或换协议类型时复用上一次结果，
     * 把 A 的节点（含 passwd/uuid）发给 B，或让 type 过滤静默失效。
     */
    private array $servers = [];

    /** 获取节点失败时的占位配置（failedProxyReturn 写入），优先级高于节点缓存 */
    private ?array $failedServers = null;

    private User $user;

    public function __construct(?User $user = null)
    {
        $this->user = $user ?? auth()->user();
    }

    public function buildClientConfig(?string $target = null, ?int $type = null): string
    { // 构建客户端配置
        $servers = $this->getServers($type);

        // 尝试匹配特定客户端
        $classes = glob(app_path('Utils/Clients').'/*.php');

        foreach ($classes as $file) {
            $className = 'App\\Utils\\Clients\\'.basename($file, '.php');

            if (! class_exists($className)) {
                continue;
            }

            $reflection = new ReflectionClass($className);

            if (! $reflection->hasConstant('AGENT')) {
                continue;
            }

            $agents = $reflection->getConstant('AGENT');
            if (! is_array($agents)) {
                continue;
            }

            foreach ($agents as $agent) {
                if ($target !== null && str_contains($target, $agent)) {
                    return (new $className)->getConfig($servers, $this->user, $target);
                }
            }
        }

        // 默认返回 URL 方案
        return URLSchemes::build($servers);
    }

    private function getServers(?int $type): array
    {
        if ($this->failedServers !== null) { // 已判定获取失败，复用占位配置，顺带避免重复查库
            return $this->failedServers;
        }

        $key = $type ?? 'all'; // 按 type 分键，否则一次订阅会把别的协议类型的节点也发出去

        if (! array_key_exists($key, $this->servers)) {
            $servers = $this->fetchAvailableNodes($type);

            if (empty($servers)) {
                // $type 未指定时是 null，不能直接传给 failedProxyReturn 的 int 形参
                $this->failedProxyReturn(trans('errors.subscribe.none'), $type ?? 0);

                return $this->failedServers;
            }

            $this->servers[$key] = $servers;
        }

        return $this->servers[$key];
    }

    public function fetchAvailableNodes(?int $type = null, bool $withConfigs = true): array|Collection
    { // 获取用户可用的节点列表
        $query = $this->user->nodes()
            ->whereIn('is_display', [2, 3]) // 获取这个账号可用节点
            ->with(['labels', 'country', 'relayNode']); // 预加载关联关系以避免N+1查询

        if ($type !== null) {
            $carrier = ProxyType::tryFrom($type)?->carrier();

            // 同一承载协议出同一套字段，SSR 与 VNET 因此要一起取
            if ($carrier) {
                $query->whereIn('type', ProxyType::carriedValues($carrier));
            } else {
                $query->whereType($type);
            }
        }

        // 根据配置决定是否随机排序，并应用数量限制
        if (sysConfig('rand_subscribe')) {
            $query->inRandomOrder();
        } else {
            $query->orderByDesc('node.sort')->orderBy('node.id');
        }

        // 限制最大订阅数量
        $max = (int) sysConfig('subscribe_max');
        if ($max) {
            $query->limit($max);
        }

        $nodes = $query->get();

        if ($withConfigs) {
            $configs = [];
            foreach ($nodes as $node) {
                $configs[] = $this->generateNodeConfig($node);
            }

            return $configs;
        }

        return $nodes;
    }

    public function generateNodeConfig(Node $node): array
    { // 生成节点的完整配置
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

        // 如果是中转节点，处理父节点配置
        if ($node->relay_node_id) {
            $parentConfig = $this->generateNodeConfig($node->relayNode);
            $config = array_merge($config, Arr::except($parentConfig, ['id', 'name', 'host', 'group', 'udp']));

            if ($parentConfig['type'] === 'trojan') {
                $config['sni'] = $parentConfig['host'];
            }

            $config['port'] = $node->port;

            return $config;
        }

        // 按节点类型生成特定配置
        $protocol = ProxyType::tryFrom($node->type);

        return match ($protocol) {
            ProxyType::SHADOWSOCKS => [
                'type' => $protocol->clientType(),
                'port' => $node->port ?: $this->user->port,
                'passwd' => $this->user->passwd,
                ...$node->profile,
            ],
            ProxyType::V2RAY => [
                'type' => $protocol->clientType(),
                'port' => $node->port,
                'uuid' => $this->user->vmess_id,
                ...$node->profile,
            ],
            ProxyType::TROJAN => [
                'type' => $protocol->clientType(),
                'port' => $node->port,
                'passwd' => $this->user->passwd,
                ...$node->profile,
            ],
            ProxyType::HYSTERIA2 => [
                'type' => $protocol->clientType(),
                'port' => $node->port,
                'passwd' => $this->user->port.':'.$this->user->passwd,
                ...$node->profile,
            ],
            default => array_merge(
                [ // SHADOWSOCKSR 与 VNET
                    'type' => ($protocol ?? ProxyType::SHADOWSOCKSR)->clientType(),
                    ...$node->profile,
                ],
                ($node->isSinglePort() && $node->port)
                    ? [ // 单端口使用中转的端口；前缀是用户端口号——它只当账号编号用，节点靠它反查密码与记账
                        'port' => $node->port,
                        'protocol_param' => $this->user->port.':'.$this->user->passwd,
                    ]
                    : [
                        'port' => $this->user->port,
                        'passwd' => $this->user->passwd,
                    ],
                $node->type === ProxyType::SHADOWSOCKSR->value
                    ? [
                        'method' => $this->user->method,
                        'protocol' => $this->user->protocol,
                        'obfs' => $this->user->obfs,
                    ]
                    : []
            ),
        } + $config;
    }

    public function failedProxyReturn(string $message, int $type = 0): void
    { // 设置错误代理返回（用于兼容客户端）
        $protocol = ProxyType::tryFrom($type) ?? ProxyType::SHADOWSOCKS;
        // 占位字段按承载协议给：VNET(4) 与 SSR(1) 同一套，客户端格式化器读的都是 allow_insecure
        $addition = match ($protocol->carrier()) {
            ProxyType::SHADOWSOCKSR => ['method' => 'none', 'passwd' => 'error', 'obfs' => 'origin', 'obfs_param' => '', 'protocol' => 'plain', 'protocol_param' => ''],
            ProxyType::V2RAY => ['uuid' => '0', 'v2_alter_id' => 0, 'method' => 'auto'],
            ProxyType::TROJAN => ['passwd' => 'error'],
            ProxyType::HYSTERIA2 => ['passwd' => 'error', 'sni' => 'error', 'allow_insecure' => false],
            default => ['method' => 'none', 'passwd' => 'error'],
        };

        $this->failedServers = [['id' => 0, 'name' => $message, 'type' => $protocol->clientType(), 'host' => sysConfig('website_url'), 'port' => 0, 'udp' => 0, ...$addition]];
    }

    public function getUserProxyConfig(Node $node, bool $isUrlFormat = false): string
    { // 获取用户显示用代理信息
        $server = $this->generateNodeConfig($node);
        $type = $isUrlFormat ? new URLSchemes : new Text;

        return match ($server['type']) {
            'shadowsocks' => $type->buildShadowsocks($server),
            'shadowsocksr' => $type->buildShadowsocksr($server),
            'vmess' => $type->buildVmess($server),
            'trojan' => $type->buildTrojan($server),
            'hysteria2' => $type->buildHysteria2($server),
            default => throw new Exception('Unsupported proxy type: '.$server['type']),
        };
    }

    public function setUser(User $user): void
    { // 设置用户
        $this->user = $user;

        // 换用户必须清缓存，否则会把上一个用户已生成的节点配置连同凭据一起发给新用户
        $this->servers = [];
        $this->failedServers = null;
    }
}
