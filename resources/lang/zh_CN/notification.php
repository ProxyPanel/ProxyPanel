<?php

declare(strict_types=1);

return [
    'attribute' => '通知',
    'new' => '您有:num条新消息',
    'empty' => '您当前没有新消息',
    'payment_received' => '您的订单支付成功，金额为:amount，请点击查看订单详情',
    'account_expired' => '账号过期提醒',
    'account_expired_content' => '您的账号将在:days天后过期，为避免影响正常使用，请及时续费',
    'account_expired_blade' => '您的账号将于:days天后过期，请及时续费',
    'active_email' => '请在30分钟内完成验证',
    'close_ticket' => '工单编号:id，标题:title已被关闭',
    'view_web' => '访问我们的官网',
    'view_ticket' => '查看此工单进度',
    'new_ticket' => '您的工单:title收到新的回复，请前往查看',
    'reply_ticket' => '工单回复：:title',
    'ticket_content' => '工单内容：',
    'node_block' => '节点阻断警告通知',
    'node_offline' => '节点离线警告',
    'node_offline_content' => '以下节点异常，可能已经离线：',
    'block_report' => '详细阻断日志：',
    'traffic_warning' => '流量使用提醒',
    'traffic_remain' => '您的流量已使用:percent%，请合理安排使用',
    'traffic_tips' => '请注意流量重置日，合理使用流量或在耗尽后充值',
    'verification_account' => '账号验证通知',
    'verification' => '您的验证码为：',
    'verification_limit' => '请在:minutes分钟内完成验证',
    'data_anomaly' => '流量异常用户提醒',
    'data_anomaly_content' => '用户:id，最近1小时流量（上传:upload，下载:download，总计:total）',
    'node' => [
        'upload' => '上传流量',
        'download' => '下载流量',
        'total' => '总流量',
    ],
];
