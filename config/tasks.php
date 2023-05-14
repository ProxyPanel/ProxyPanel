<?php

return [
    'chunk' => env('TASKS_CHUNK', 1000), // 大数据量修改，分段处理，减少内存使用
    'clean' => [
        'node_daily_logs' => env('TASKS_NODE_DAILY_LOGS', '-2 month'), // 清除节点每天流量数据日志
        'node_hourly_logs' => env('TASKS_NODE_HOURLY_LOGS', '-3 days'), // 清除节点每小时流量数据日志
        'notification_logs' => env('TASKS_NOTIFICATION_LOGS', '-1 month'), // 清理通知日志
        'node_heartbeats' => env('TASKS_NODE_HEARTBEATS', '-30 minutes'), // 清除节点负载信息日志
        'node_online_logs' => env('TASKS_NODE_ONLINE_LOGS', '-1 hour'), // 清除节点在线用户数日志
        'payments' => env('TASKS_PAYMENTS', '-1 year'), // 清理在线支付日志
        'rule_logs' => env('TASKS_RULE_lOGS', '-3 month'), // 清理审计触发日志
        'node_online_ips' => env('TASKS_NODE_ONLINE_IPS', '-1 week'), // 清除用户连接IP
        'user_baned_logs' => env('TASKS_USER_BANED_LOGS', '-3 month'), // 清除用户封禁日志
        'user_daily_logs_nodes' => env('TASKS_USER_DAILY_LOGS_NODES', '-1 month'), // 清除用户各节点的每天流量数据日志
        'user_daily_logs_total' => env('TASKS_USER_DAILY_LOGS_TOTAL', '-3 month'), // 清除用户节点总计的每天流量数据日志
        'user_hourly_logs' => env('TASKS_USER_HOURLY_LOGS', '-3 days'), // 清除用户每时各流量数据日志 最少值为 2
        'login_logs' => env('TASKS_LOGIN_LOGS', '-3 month'), // 清除用户登陆日志
        'subscribe_logs' => env('TASKS_SUBSCRIBE_LOGS', '-1 month'), // 清理用户订阅请求日志
        'traffic_logs' => env('TASKS_TRAFFIC_LOGS', '-3 days'), // 清除用户流量日志
        'unpaid_orders' => env('UNPAID_ORDERS', '-1 year'), // 清除用户流量日志
    ],
    'close' => [
        'tickets' => env('TASKS_TICKETS', 72), // 自动关闭工单，单位：小时
        'confirmation_orders' => env('TASKS_CONFIRMATION_ORDERS', 12), // 自动关闭人工支付订单，单位：小时
        'orders' => env('TASKS_ORDERS', 15), // 自动关闭订单，单位：分钟
        'verify' => env('TASKS_VERIFY', 15), // 自动失效验证码，单位：分钟
    ],
    'release_port' => env('TASKS_RELEASE_PORT', 30), // 端口自动释放，单位：天
    'recently_heartbeat' => env('TASKS_RECENTLY_HEARTBEAT', '-10 minutes'), // 节点近期负载
];
