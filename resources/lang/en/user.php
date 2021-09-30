<?php

return [
    'account'             => [
        'credit'           => 'Credit',
        'status'           => 'Status',
        'level'            => 'Level',
        'group'            => 'Group',
        'speed_limit'      => 'Speed Limit',
        'remain'           => 'Remain Data',
        'time'             => 'Period',
        'last_login'       => 'Last Login',
        'reset'            => ':days days to next reset',
        'connect_password' => 'Proxy Connect Password',
        'reason'           => [
            'normal'            => 'Normal',
            'expired'           => 'Expired',
            'overused'          => 'You have reach the <code>:data</code> GB hourly data spend limit<br/> Wait <code id="countdown">:min</code> to cool down',
            'traffic_exhausted' => 'OUT OF DATA',
            'unknown'           => 'UNKNOWN ERROR, please contact admin for reason',
        ],
    ],
    'status'              => [
        'disabled'        => 'Disabled',
        'enabled'         => 'Normal',
        'expired'         => 'Expired',
        'limited'         => 'Limited',
        'unused'          => 'Unused',
        'used'            => 'Used',
        'closed'          => 'Closed',
        'completed'       => 'Completed',
        'waiting_payment' => 'Waiting for payment',
        'using'           => 'Using',
        'waiting_confirm' => 'Waiting for confirm',
        'prepaid'         => 'Prepaid',
        'applying'        => 'Applying',
        'withdrawn'       => 'Withdrawn',
        'not_withdrawn'   => 'Not withdrawn',
        'reply'           => 'Replied',
        'pending'         => 'Pending',
    ],
    'home'                => [
        'attendance'         => [
            'attribute' => 'Attendance',
            'disable'   => 'Attendance System is disabled',
            'done'      => 'You have already signed in, Come back tomorrow!',
            'success'   => 'You got :data traffic',
            'failed'    => 'System ❌ Error',
        ],
        'traffic_logs'       => 'Data Records',
        'announcement'       => 'Announcement',
        'wechat_push'        => 'WeChat Notification Service',
        'chat_group'         => 'Chat Group',
        'empty_announcement' => 'No Announcement',
    ],
    'purchase_to_unlock'  => 'Unlock after purchasing service',
    'purchase_required'   => 'This feature is disabled for non-paying users! Please',
    'more'                => 'More',
    'attribute'           => [
        'node'    => 'Node',
        'data'    => 'Data',
        'ip'      => 'IP Address',
        'isp'     => 'ISP',
        'address' => 'Address',
    ],
    'purchase_promotion'  => 'Purchase Now !',
    'menu'                => [
        'helps'           => 'Help',
        'home'            => 'Home',
        'invites'         => 'Invite',
        'invoices'        => 'Invoice',
        'nodes'           => 'Node',
        'referrals'       => 'Affiliates',
        'shop'            => 'Shop',
        'profile'         => 'Settings',
        'tickets'         => 'Ticket',
        'admin_dashboard' => 'Dashboard',
    ],
    'contact'             => 'Contact Info',
    'oauth'               => [
        'bind_title' => 'Binding Your Social Accounts',
        'not_bind'   => 'Not Yet Bound',
        'bind'       => 'Bind',
        'rebind'     => 'Rebind',
        'unbind'     => 'Unbind',
    ],
    'coupon'              => [
        'attribute' => 'Coupon',
        'voucher'   => 'Voucher',
        'recharge'  => 'Gift Card',
        'discount'  => 'Discount',
        'error'     => [
            'unknown'  => 'unKnown Coupon',
            'used'     => 'This Coupon has been used.',
            'expired'  => 'Out of Date',
            'run_out'  => 'Run Out of Usage',
            'inactive' => 'Coupon Inactive yet',
            'wait'     => 'The Event will begin until :time, Please wait',
            'limit'    => 'Order\'s price is reach the minimum requirement of this coupon',
            'higher'   => 'The minimum requirement of this coupon is ¥:amount',
        ],
    ],
    'error_response'      => 'Something went wrong, please try again later.',
    'invite'              => [
        'attribute'       => 'Invitation code',
        'total'           => 'Total: num invitation codes',
        'tips'            => 'Can generate <strong> :num <strong> invitation codes, valid within :days',
        'logs'            => 'Invitation Record',
        'promotion'       => 'Register and activate with your invitation code, Both sides will get <mark>:traffic</mark> traffic as rewards; when invitees purchase services, you will get <mark>:referral_percent%</mark> of their spend amount as commission.',
        'generate_failed' => 'Failed to generate',
    ],
    'reset_data'          => [
        ''          => 'Reset Data',
        'required'  => 'Required',
        'cost_tips' => 'This following action will cost ¥:amount！',
        'lack'      => 'No enough Credit!',
        'logs'      => 'User purchase reset data',
        'success'   => 'Reset Successfully',
    ],
    'referral'            => [
        'link'       => 'Referral link',
        'total'      => 'Total rebate ¥:amount (:total times), you can apply for cash withdrawal when it reach  ¥:money.',
        'amount'     => 'Spend amount',
        'commission' => 'Rebate amount',
        'logs'       => 'Commission records',
        'failed'     => 'Application failed',
        'success'    => 'Application success',
        'msg'        => [
            'account'     => 'Account has expired, please purchase to active service first',
            'applied'     => 'There is an application exist, please wait for the previous application to be processed',
            'unfulfilled' => 'Minimal amount to created application is ¥:amount',
            'wait'        => 'Please wait for the administrator to review',
            'error'       => 'Rebate order creation failed, please try later or notify the administrator',
        ],
    ],
    'inviter'             => 'Inviter',
    'invitee'             => 'Invitee',
    'consumer'            => 'Consumer',
    'unknown'             => 'Unknown',
    'registered_at'       => 'Registration at',
    'bought_at'           => 'Purchased at',
    'payment_method'      => 'payment method',
    'pay'                 => 'Pay',
    'input_coupon'        => 'Please enter the gift code',
    'recharge'            => 'Pay',
    'recharge_credit'     => 'Add Funds',
    'recharging'          => 'Paying ...',
    'withdraw_commission' => 'Withdraw Commission',
    'withdraw_at'         => 'Withdraw Date',
    'withdraw_logs'       => 'Withdrawals records',
    'withdraw'            => 'Withdraw',
    'scan_qrcode'         => 'Please scan the QR code',
    'shop'                => [
        'hot'           => 'HOT',
        'limited'       => 'LIMIT',
        'change_amount' => 'Amount',
        'buy'           => 'Purchase',
        'description'   => 'Description',
        'service'       => 'Service',
        'pay_credit'    => 'Pay By Credit',
        'pay_online'    => 'Pay By Online payment',
        'price'         => 'Price',
        'quantity'      => 'Quantity',
        'subtotal'      => 'Subtotal',
        'total'         => 'Total',
        'conflict'      => 'Service Conflict',
        'conflict_tips' => '<p>Current order will be set as <code>Prepaid order</code><p><ol class="text-left"><li> Prepaid order will be active after current service expired!</li><li> Your also can active the order manually</li></ol>',
        'call4help'     => 'Please create a ticket to ask customer service',
    ],
    'payment'             => [
        'error'           => 'The recharge balance is not compliant',
        'creating'        => 'Creating payment order...',
        'redirect_stripe' => 'Redirect to Stripe',
        'qrcode_tips'     => 'Please using <strong class="red-600">:software</strong> to scan QrCode',
        'close_tips'      => 'Please complete payment in <code>:minutes minutes</code>, otherwise it will be auto-closed by system',
        'mobile_tips'     => '<strong>Mobile User</strong>：Press QrCode image for a short amount of time -> Save Images -> Open payment software -> Scan it',
    ],
    'invoice'             => [
        'attribute'               => 'Order',
        'id'                      => 'Order number',
        'detail'                  => 'Order Detail',
        'amount'                  => 'Amount',
        'active_prepaid_question' => 'Are you sure to active prepaid order?',
        'active_prepaid_tips'     => 'After active：<br>Current order will be set to expired! <br> Expired dates will be recalculated!',
    ],
    'service'             => [
        'node_count' => 'Include <code>:num</code> Nodes',
        'unlimited'  => 'Unlimited Speed',
    ],
    'node'                => [
        'info'     => 'Configuration information',
        'setting'  => 'Proxy settings',
        'unstable' => 'Node fluctuation/under maintenance',
        'rate'     => ':ratio time data consumption',
    ],
    'subscribe'           => [
        'baned'            => 'Your subscription function is disabled, please contact the administrator to restore',
        'link'             => 'Subscribe Link',
        'tips'             => 'Warning：Subscribe Link is for personal used only, Please do not show to anyone else. Otherwise, they may using your service without your permission',
        'exchange_warning' => 'Exchange Link:\n1. Old Link will be disabled\n2. Proxy connection password will be reset',
        'ss_only'          => 'Subscribe SS/SSR Only',
        'v2ray_only'       => 'Subscribe V2Ray Only',
        'trojan_only'      => 'Subscribe Trojan Only',
        'error'            => 'Exchange Link Error',
    ],
    'ticket'              => [
        'attribute'         => 'Ticket',
        'submit_tips'       => 'Submit Ticket?',
        'reply_confirm'     => 'Reply Ticket?',
        'close_tips'        => 'Are you sure to close this ticket?',
        'close'             => 'Close Ticket',
        'failed_closed'     => 'Error: Ticket has been closed',
        'reply_placeholder' => 'Say something',
        'reply'             => 'Reply',
        'close_msg'         => 'Ticket ID :id has been closed by user',
        'placeholder'       => 'Please describe your questions in detail',
        'title_placeholder' => 'Please describe your questions in few key words',
        'new'               => 'Create Ticket',
        'working_hour'      => 'Customer Service',
        'online_hour'       => 'Working Hours',
        'service_tips'      => 'We have many way to contact, please choose <code> ONE </code> of them！ <br> Duplicate request will cost the service to delay',
        'error'             => 'Error！Please contact Customer Service',
    ],
    'traffic_logs'        => [
        '24hours' => 'Today Traffic Usage',
        '30days'  => 'Daily Traffic Usage',
        'tips'    => 'Tips: Traffic logs has delays!',
    ],
    'client'              => 'Clients',
    'tutorials'           => 'Tutorials',
    'current_role'        => 'Current Role as',
];
