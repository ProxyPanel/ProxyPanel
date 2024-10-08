<?php

declare(strict_types=1);

return [
    'forbidden' => [
        'access' => '检测到未知IP或代理访问，禁止访问',
        'bots' => '检测到机器人访问，禁止访问',
        'china' => '检测到中国IP或代理访问，禁止访问',
        'oversea' => '检测到海外IP或代理访问，禁止访问',
        'redirect' => '识别到(:ip :url)通过订阅链接访问，强制重定向',
        'unknown' => '未知禁止访问模式！请在系统设置中修改【禁止访问模式】！',
    ],
    'get_ip' => '获取IP信息异常',
    'log' => '日志',
    'refresh' => '刷新',
    'refresh_page' => '请刷新页面后，再访问',
    'report' => '错❌误携带了报告：',
    'safe_code' => '请输入安全码',
    'safe_enter' => '安全入口访问',
    'subscribe' => [
        'banned_until' => '账号封禁至:time，请解封后再更新！',
        'expired' => '账号已过期，请续费！',
        'none' => '无可用节点',
        'out' => '流量耗尽，请重新购买或重置流量！',
        'question' => '账号存在问题，请前往官网查询！',
        'sub_banned' => '链接已被封禁，请前往官网查询原因',
        'unknown' => '使用了错误的链接，请重新获取链接！',
        'user' => '使用了错误的链接，账号不存在！请重新获取链接！',
        'user_disabled' => '账号被禁用！',
    ],
    'title' => '⚠️错误触发',
    'unsafe_enter' => '非安全入口访问',
    'visit' => '请访问',
    'whoops' => '哎呦！',
];
