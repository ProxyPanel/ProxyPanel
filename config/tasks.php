<?php

return [
    'chunk'              => 1000, // 大数据量修改，分段处理，减少内存使用
    'clean'              => [
        'node_daily_logs'       => '-2 month', // 清除节点每天流量数据日志
        'node_hourly_logs'      => '-3 days', // 清除节点每小时流量数据日志
        'notification_logs'     => '-1 month', // 清理通知日志
        'node_heartbeats'       => '-30 minutes', // 清除节点负载信息日志
        'node_online_logs'      => '-1 hour', // 清除节点在线用户数日志
        'payments'              => '-1 year', // 清理在线支付日志
        'rule_logs'             => '-3 month', // 清理审计触发日志
        'node_online_ips'       => '-1 week', // 清除用户连接IP
        'user_baned_logs'       => '-3 month', // 清除用户封禁日志
        'user_daily_logs_nodes' => '-1 month', // 清除用户各节点的每天流量数据日志
        'user_daily_logs_total' => '-3 month', // 清除用户节点总计的每天流量数据日志
        'user_hourly_logs'      => '-3 days', // 清除用户每时各流量数据日志
        'login_logs'            => '-3 month', // 清除用户登陆日志
        'subscribe_logs'        => '-1 month', // 清理用户订阅请求日志
        'traffic_logs'          => '-3 days', // 清除用户流量日志
    ],
    'close'              => [
        'ticket' => 72, // 自动关闭工单，单位：小时
        'order'  => 15, // 自动关闭订单，单位：分钟
        'verify' => 15, // 自动失效验证码，单位：分钟
    ],
    'release_port'       => 30, // 端口自动释放，单位：天
    'recently_heartbeat' => '-10 minutes', // 节点近期负载
];
