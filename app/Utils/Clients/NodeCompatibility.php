<?php

namespace App\Utils\Clients;

use App\Enums\ProxyType;
use App\Models\Node;
use App\Utils\Clients\Formatters\Clash;
use App\Utils\Clients\Formatters\ClashMeta;
use App\Utils\Clients\Formatters\Stash;

/**
 * 订阅出口的算法兼容性判据：哪个客户端会因为算法值没实现而整条丢弃这个节点.
 *
 * 判据只有一份 —— 调的就是各出口自己的构建器，不另存算法名单。丢弃本身是保护性的：
 * Clash 遇到不支持的算法是整个配置解析失败，同 provider 的其它节点一起作废。
 */
class NodeCompatibility
{
    /** 只有 Clash 家族按算法值过滤，其余出口只按类型名单取舍 */
    private const GATED = [Clash::class, ClashMeta::class, Stash::class];

    /** gaps() 的键给人读时的产品名（都是专有名词，不进语言文件） */
    public const LABELS = [
        Clash::class => 'Clash',
        ClashMeta::class => 'Clash.Meta',
        Stash::class => 'Stash',
    ];

    /**
     * 每个类型参与校验的字段，各配一个三家都收得下的取值，用来定位是哪个字段把节点挡在门外.
     *
     * @var array<string, array<string, string|null>>
     */
    private const PROBE = [
        'shadowsocks' => ['method' => 'aes-256-cfb'],
        'shadowsocksr' => ['method' => 'aes-256-cfb', 'protocol' => 'origin', 'obfs' => 'plain'],
        'vmess' => ['method' => 'auto', 'v2_net' => 'ws'],
        'hysteria2' => ['obfs' => null],
    ];

    /** 把 gaps() 的键值拼成后台提示要的两段文本 */
    public static function describe(array $gaps): array
    {
        return [
            'clients' => implode(', ', array_map(fn (string $class): string => self::LABELS[$class] ?? $class, array_keys($gaps))),
            'fields' => implode(', ', array_values(array_unique($gaps))),
        ];
    }

    /**
     * @return array<string, string> 客户端类名 => 被挡的字段名
     */
    public static function gaps(array $server): array
    {
        $type = $server['type'] ?? null;
        $fields = self::PROBE[$type] ?? [];
        $builder = 'build'.ucfirst($type ?? '');
        $gaps = [];

        foreach (self::GATED as $class) {
            // 类型不在该客户端名单里是声明式的能力边界，不是静默丢弃
            if (! $fields || ! in_array($type, $class::types(), true)) {
                continue;
            }

            if ($class::$builder($server)) {
                continue;
            }

            foreach ($fields as $field => $probe) {
                $candidate = $server;
                $candidate[$field] = $probe;

                if ($class::$builder($candidate)) {
                    $gaps[$class] = $field;

                    break;
                }
            }

            // 逐字段都换成正值仍出得来，说明卡在名单之外的判据，也要报出来而不是当成没问题
            $gaps[$class] ??= 'config';
        }

        return $gaps;
    }

    /**
     * 只看节点自己能决定的算法：SSR(type=1) 的 method/protocol/obfs 在订阅里由用户记录覆盖，按节点判会误导.
     *
     * 只拼构建器会读的字段，不走 ProxyService::generateNodeConfig()：那条路径要取 $node->country->name，
     * country_code 过期就能把后台列表页打成 500，而那正是管理员用来修它的页面。
     */
    public static function forNode(Node $node): array
    {
        if ((int) $node->type === ProxyType::SHADOWSOCKSR->value) {
            return [];
        }

        return self::gaps([
            'type' => ProxyType::tryFrom((int) $node->type)?->clientType(),
            'name' => $node->name,
            'host' => $node->host,
            'port' => $node->port,
            'udp' => $node->is_udp,
            'passwd' => '',
            'uuid' => '',
            'method' => '',
            'protocol' => '',
            'obfs' => '',
            'obfs_param' => '',
            'protocol_param' => '',
            'v2_alter_id' => 0,
            'v2_net' => '',
            'v2_host' => '',
            'v2_path' => '',
            'v2_tls' => '',
            ...($node->profile ?? []),
        ]);
    }
}
