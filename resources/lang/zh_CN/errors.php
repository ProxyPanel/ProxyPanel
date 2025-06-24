<?php

declare(strict_types=1);

return [
    'forbidden' => [
        'access' => '检测到未知 IP 或代理，已禁止访问！',
        'bots' => '检测到机器人访问，已禁止访问！',
        'china' => '检测到中国 IP 或代理，已禁止访问！',
        'oversea' => '检测到海外 IP 或代理，已禁止访问！',
        'redirect' => '检测到 (:ip :url) 使用订阅链接访问，已强制重定向',
        'unknown' => '未知拦截模式，请在系统设置中检查配置！',
    ],
    'get_ip' => '获取 IP 信息失败',
    'log' => '日志',
    'refresh' => '刷新',
    'refresh_page' => '请刷新页面后重试',
    'report' => '错误报告内容：',
    'safe_code' => '请输入安全码',
    'safe_enter' => '安全入口访问',
    'subscribe' => [
        'banned_until' => '账号已封禁至 :time，请解封后再尝试更新！',
        'expired' => '账号已过期，请续费后使用！',
        'none' => '暂无可用节点',
        'out' => '流量已用尽，请购买或重置流量！',
        'question' => '账号存在异常，请前往官网查询详情！',
        'sub_banned' => '订阅链接已被封禁，请前往官网了解原因！',
        'unknown' => '订阅链接无效，请重新获取！',
        'user' => '链接无效，账号不存在，请重新获取！',
        'user_disabled' => '账号已被禁用！',
    ],
    'title' => '⚠️ 发生错误',
    'unsafe_enter' => '非安全入口访问',
    'visit' => '请访问',
    'whoops' => '哎呀！',
];
