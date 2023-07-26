<?php

declare(strict_types=1);

return [
    'attribute' => 'Notification',
    'new' => '{1} :num new message|[1,*] :num new messages',
    'empty' => 'You have no new messages',
    'payment_received' => 'Payment received, amount: :amount. View order details',
    'account_expired' => 'Account expiration reminder',
    'account_expired_content' => 'Your account will expire in :days days. Please renew promptly to continue using our services.',
    'account_expired_blade' => 'Account will expire in :days days, please renew promptly',
    'active_email' => 'Please complete verification within 30 minutes',
    'close_ticket' => 'Ticket :id: :title closed',
    'view_web' => 'View website',
    'view_ticket' => 'View ticket',
    'new_ticket' => 'New ticket received: :title',
    'reply_ticket' => 'Ticket replied: :title',
    'ticket_content' => 'Ticket content:',
    'node_block' => 'Node block warning',
    'node_offline' => 'Node offline warning',
    'node_offline_content' => 'Abnormal nodes, may be offline:',
    'block_report' => 'Block report:',
    'traffic_warning' => 'Data usage warning',
    'traffic_remain' => ':percent% of data used, please pay attention',
    'traffic_tips' => 'Please note the data reset date and use data rationally, or renew after exhausted',
    'verification_account' => 'Account verification',
    'verification' => 'Your verification code:',
    'verification_limit' => 'Please verify within :minutes minutes',
    'data_anomaly' => 'Data anomaly user warning',
    'data_anomaly_content' => 'User :id: [Upload: :upload | Download: :download | Total: :total] in last 1 hour',
    'node' => [
        'upload' => 'Upload',
        'download' => 'Download',
        'total' => 'Total',
    ],
];
