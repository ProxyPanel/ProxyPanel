<?php

namespace App\Utils\Clients\Formatters;

trait TypeDispatch
{
    /** 本出口能下发的协议名单：名单外的 type 整条跳过，名单内但构建器回空值也算命中（丢弃还是拼接由调用方决定） */
    abstract public static function types(): array;

    /** 逐个类型构建并收成 ['ids' => 命中的节点 id, 'proxies' => 代理配置列表]，供 YAML 类出口合并进模板 */
    protected static function assembleProxies(array $servers): array
    {
        $ids = [];
        $proxies = [];

        foreach (static::matched($servers) as [$server, $proxy]) {
            if ($proxy) {
                $ids[] = $server['id'];
                $proxies[] = $proxy;
            }
        }

        return ['ids' => $ids, 'proxies' => $proxies];
    }

    /** 按类型名单分派到本类的 build{Type}()，返回命中项的 [节点配置, 生成结果] 列表 */
    protected static function matched(array $servers): array
    {
        $types = static::types();
        $matched = [];

        foreach ($servers as $server) {
            if (in_array($server['type'], $types, true)) {
                $matched[] = [$server, call_user_func([static::class, 'build'.ucfirst($server['type'])], $server)];
            }
        }

        return $matched;
    }
}
