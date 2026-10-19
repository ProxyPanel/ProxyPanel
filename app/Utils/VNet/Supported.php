<?php

namespace App\Utils\VNet;

/**
 * VNet-SSR 后端实际实现了哪些算法名 —— 这是节点仓那份名单的镜像。
 *
 * 唯一来源是 VNet-SSR 的 docs/capabilities.json（由 `go run ./cmd/capabilities` 从其注册表现场生成，
 * 节点仓有测试守住文件与代码不漂移）；字段契约见同仓 docs/openapi.yaml，对账与排障步骤见 docs/operations.md。
 * 节点侧改动算法实现后要重新生成并同步这里的三组常量。
 *
 * 为什么要在保存节点时挡：名单外的名字不会让节点报错下线，节点是拒绝建连接而心跳照常上报 ——
 * 面板看到「在线、无人」，用户侧全断。SSR(type=1) 由另一套后端承载，名单更宽，不受这三个常量约束。
 */
final class Supported
{
    public const METHODS = [
        'none',
        'rc4',
        'rc4-md5',
        'rc4-md5-6',
        'bf-cfb',
        'cast5-cfb',
        'des-cfb',
        'aes-128-cfb',
        'aes-192-cfb',
        'aes-256-cfb',
        'aes-128-cfb1',
        'aes-192-cfb1',
        'aes-256-cfb1',
        'aes-128-cfb8',
        'aes-192-cfb8',
        'aes-256-cfb8',
        'aes-128-ctr',
        'aes-192-ctr',
        'aes-256-ctr',
        'aes-128-ofb',
        'aes-192-ofb',
        'aes-256-ofb',
        'aes-128-cbc',
        'aes-128-gcm',
        'aes-192-gcm',
        'aes-256-gcm',
        'salsa20',
        'chacha20',
        'chacha20-ietf',
        'chacha20-ietf-poly1305',
    ];

    /** 协议名：旧四代 auth_sha1 系（verify_simple/auth_simple/auth_sha1/v2）上游已因漏洞删除，节点不实现 */
    public const PROTOCOLS = [
        'origin',
        'auth_sha1_v4',
        'auth_aes128_md5',
        'auth_aes128_sha1',
        'auth_chain_a',
        'auth_chain_b',
        'auth_chain_c',
        'auth_chain_d',
        'auth_chain_e',
        'auth_chain_f',
    ];

    /** 混淆名：origin 与 plain 在节点侧是同一个实现；random_head 因重放攻击被上游移除，不接 */
    public const OBFS = [
        'plain',
        'origin',
        'http_simple',
        'http_post',
        'tls1.2_ticket_auth',
        'tls1.2_ticket_fastauth',
    ];
}
