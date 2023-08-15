<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'users' => 'Total Users',
        'available_users' => 'Active Users',
        'paid_users' => 'Paying Users',
        'active_days_users' => 'Active Users in Last :days Days',
        'inactive_days_users' => 'Inactive Users for Over :days Days',
        'online_users' => 'Online Now',
        'expiring_users' => 'Expiring Soon',
        'overuse_users' => 'Data Overuse [≥90%] Users',
        'abnormal_users' => 'Abnormal Traffic in Past Hour',
        'nodes' => 'Nodes',
        'maintaining_nodes' => 'Maintenance Mode Nodes',
        'current_month_traffic_consumed' => 'Traffic Used This Month',
        'days_traffic_consumed' => 'Traffic Used in Past :days Days',
        'orders' => 'Total Orders',
        'online_orders' => 'Online Payment Orders',
        'succeed_orders' => 'Paid Orders',
        'credit' => 'Total Credit',
        'withdrawing_commissions' => 'Pending Commissions',
        'withdrawn_commissions' => 'Withdrawn Commissions',
    ],
    'action' => [
        'edit_item' => 'Edit :attribute',
        'add_item' => 'Add :attribute',
    ],
    'confirm' => [
        'delete' => [0 => 'Confirm Delete :attribute [', 1 => ']?'],
        'continues' => 'Confirm to continue?',
        'export' => 'Confirm to export all?',
    ],
    'user_dashboard' => 'User Dashboard',
    'menu' => [
        'dashboard' => 'Dashboard',
        'user' => [
            'attribute' => 'Users',
            'list' => 'User Management',
            'oauth' => 'OAuth',
            'group' => 'User Groups',
            'credit_log' => 'Credit History',
            'subscribe' => 'Subscriptions',
        ],
        'rbac' => [
            'attribute' => 'RBAC',
            'permission' => 'Permissions',
            'role' => 'Roles',
        ],
        'customer_service' => [
            'attribute' => 'Helpdesk',
            'ticket' => 'Support Tickets',
            'article' => 'Knowledge Base',
            'push' => 'Push Notifications',
            'mail' => 'Email',
        ],
        'node' => [
            'attribute' => 'Nodes',
            'list' => 'Node Management',
            'auth' => 'API Authorization',
            'cert' => 'Certificates',
        ],
        'rule' => [
            'attribute' => 'Audit Rules',
            'list' => 'Rules',
            'group' => 'Rule Groups',
            'trigger' => 'Trigger Records',
        ],
        'shop' => [
            'attribute' => 'Shop',
            'goods' => 'Products',
            'coupon' => 'Coupons',
            'order' => 'Orders',
        ],
        'promotion' => [
            'attribute' => 'Affiliates',
            'invite' => 'Referrals',
            'withdraw' => 'Withdraws',
            'rebate_flow' => 'Rebate History',
        ],
        'analysis' => [
            'attribute' => 'Analytics',
            'accounting' => 'Accounting',
            'user_flow' => 'User Flow',
        ],
        'log' => [
            'attribute' => 'Logs',
            'traffic' => 'Data Usage',
            'traffic_flow' => 'Data Flow',
            'service_ban' => 'Ban Records',
            'online_logs' => 'Online Logs',
            'online_monitor' => 'Online Monitoring',
            'notify' => 'Notifications',
            'payment_callback' => 'Payment Callback',
            'system' => 'System Logs',
        ],
        'tools' => [
            'attribute' => 'Tools',
            'decompile' => 'Decompile',
            'convert' => 'Convert',
            'import' => 'Import',
            'analysis' => 'Log Analysis',
        ],
        'setting' => [
            'attribute' => 'Settings',
            'email_suffix' => 'Email Filters',
            'universal' => 'General',
            'system' => 'System',
        ],
    ],
    'user' => [
        'massive' => [
            'text' => '# of Users to Generate',
            'failed' => 'User Generation Failed',
            'succeed' => 'User Generated Successfully',
            'note' => 'Backend bulk user generate',
        ],
        'proxy_info' => 'Config Info',
        'traffic_monitor' => 'Traffic Stats',
        'online_monitor' => 'Online Monitor',
        'reset_traffic' => 'Reset Data',
        'user_view' => 'Switch to User View',
        'connection_test' => 'Connection Test',
        'counts' => '<code>:num</code> accounts total',
        'reset_confirm' => [0 => 'Confirm Reset [', 1 => ']\'s data?'],
        'info' => [
            'account' => 'Account Info',
            'proxy' => 'Proxy Info',
            'switch' => 'Switch Identity',
            'reset_date_hint' => 'Next data reset date',
            'expired_date_hint' => 'Leave empty for 1 year by default',
            'uuid_hint' => 'UUID for V2Ray',
            'recharge_placeholder' => 'Input negative to deduct balance',
        ],
        'update_help' => 'Update successful, go back?',
        'proxies_config' => '[:username] Config Info',
        'group' => [
            'title' => 'User Groups <small>(Nodes can be in multiple groups, user only in one)</small>',
            'name' => 'Group Name',
            'counts' => '<code>:num</code> groups total',
        ],
    ],
    'zero_unlimited_hint' => '0 or empty for unlimited',
    'node' => [
        'traffic_monitor' => 'Traffic Stats',
        'refresh_geo' => 'Refresh Geo Data',
        'ping' => 'Latency Test',
        'connection_test' => 'Connection Test',
        'counts' => '<code>:num</code> nodes total',
        'reload_all' => 'Reload All Backends',
        'refresh_geo_all' => 'Refresh Geo Data',
        'reload_confirm' => 'Confirm Reload Node?',
        'info' => [
            'hint' => '<strong>Note:</strong> The auto-generated <code>ID</code> is the <code>node_id</code> for ShadowsocksR backend and <code>nodeId</code> for V2Ray backend',
            'basic' => 'Basic Info',
            'ddns_hint' => 'Dynamic IP nodes need <a href="https://github.com/NewFuture/DDNS" target="_blank">DDNS</a>. Connection test will use domain name',
            'domain_placeholder' => 'Server domain, will use first if filled',
            'domain_hint' => 'With DDNS enabled, domain will auto bind IP! No longer need to modify DNS record manually.',
            'extend' => 'Extended Info',
            'display' => [
                'invisible' => 'Invisible',
                'node' => 'Visible only in Node Page',
                'sub' => 'Visible only in Subscriptions',
                'all' => 'Fully Visible',
                'hint' => 'Whether visible in subscription/node list',
            ],
            'ipv4_hint' => 'Multiple IPs separated by "," ; e.g. 1.1.1.1, 8.8.8.8',
            'ipv6_hint' => 'Multiple IPs separated by "," ; e.g. 1.1.1.1, 8.8.8.8',
            'ipv4_placeholder' => 'Server IPv4 Address',
            'ipv6_placeholder' => 'Server IPv6 Address',
            'push_port_hint' => 'Required, open in firewall to avoid push failure',
            'data_rate_hint' => 'E.g. 0.1 means 100M will be count as 10M; 5 means 100M will be count as 500M',
            'level_hint' => 'Level: 0 = No ratings, all nodes visible',
            'detection' => [
                'tcp' => 'TCP only',
                'icmp' => 'ICMP only',
                'all' => 'Detect All',
                'hint' => 'Random check every 30-60 mins',
            ],
            'obfs_param_hint' => 'Fill in parameters for traffic masquerading if obfs is not [plain]; &#13;&#10;Suggest port 80 if obfs is [http_simple]; &#13;&#10;Suggest port 443 if obfs is [tls];',
            'additional_ports_hint' => 'If enabled, please configure server <span class="red-700"><a href="javascript:showTnc();">additional_ports</a></span>',
            'v2_method_hint' => 'Do not use none with WebSocket',
            'v2_net_hint' => 'Enable TLS with WebSocket',
            'v2_cover' => [
                'none' => 'None',
                'http' => 'HTTP',
                'srtp' => 'SRTP',
                'utp' => 'uTP',
                'wechat' => 'WeChat Video',
                'dtls' => 'DTLS 1.2',
                'wireguard' => 'WireGuard',
            ],
            'v2_host_hint' => 'For HTTP, separate multiple domains with ",". Only one allowed for WebSocket.',
            'v2_tls_provider_hint' => 'Different backends have different configs:',
            'single_hint' => 'Recommended port 80/443. Backend needs <br> strict mode config: only connect via specified ports. (<a href="javascript:showPortsOnlyConfig();">How to configure</a>)',
        ],
        'proxy_info' => '*Compatibility with SS',
        'proxy_info_hint' => 'For compatibility, please add <span class="red-700">_compatible</span> to protocol and obfuscation in server config',
        'reload' => 'Reload Backend',
        'auth' => [
            'title' => 'API Authorizations <small>WEBAPI</small>',
            'deploy' => [
                'title' => 'Deploy :type_label Backend',
                'attribute' => 'Backend Deployment',
                'command' => 'Instructions',
                'update' => 'Update',
                'uninstall' => 'Uninstall',
                'start' => 'Start',
                'stop' => 'Stop',
                'status' => 'Status',
                'recent_logs' => 'Recent Logs',
                'real_time_logs' => 'Real-time Logs',
                'restart' => 'Restart',
                'same' => 'Same Above',
                'trojan_hint' => 'Please fill in node <a href=":url" target="_blank">domain</a> and resolve domain DNS to node IP',
            ],
            'reset_auth' => 'Reset Key',
            'counts' => '<code>:num</code> authorizations total',
            'generating_all' => 'Generate authorization for all nodes?',
        ],
        'cert' => [
            'title' => 'Domain Certs <small>(For V2Ray node spoofing)</small>',
            'counts' => '<code>:num</code> certs total',
            'key_placeholder' => 'Domain cert KEY. Allow empty, VNET-V2Ray supports auto certs',
            'pem_placeholder' => 'Domain cert PEM. Allow empty, VNET-V2Ray supports auto certs',
        ],
    ],
    'hint' => 'Hint',
    'oauth' => [
        'title' => 'User OAuth',
        'counts' => '<code>:num</code> authorizations total',
    ],
    'select_all' => 'Select All',
    'clear' => 'Clear',
    'unselected_hint' => 'To Assign, Search Here',
    'selected_hint' => 'Assigned, Search Here',
    'clone' => 'Clone',
    'monitor' => [
        'daily_chart' => 'Daily Traffic',
        'monthly_chart' => 'Monthly Traffic',
        'node' => 'Node Traffic',
        'user' => 'User Traffic',
        'hint' => '<strong>Hint:</strong> Check scheduled tasks if no data',
    ],
    'tools' => [
        'analysis' => [
            'title' => 'SSR Log Analysis <small>For single node</small>',
            'req_url' => 'Recent Request URLs',
            'not_enough' => 'Less than 15,000 records, unable to analyze',
        ],
        'convert' => [
            'title' => 'Format Conversion <small>SS to SSR</small>',
            'content_placeholder' => 'Please fill in the config to convert',
        ],
        'decompile' => [
            'title' => 'Decompile <small>Config Info</small>',
            'attribute' => 'Decompile',
            'content_placeholder' => 'Please fill in the SSR links to decompile, one per line',
        ],
    ],
    'ticket' => [
        'title' => 'Tickets',
        'counts' => '<code>:num</code> tickets total',
        'send_to' => 'Please fill in target user details',
        'user_info' => 'User Info',
        'inviter_info' => 'Inviter Info',
        'close_confirm' => 'Confirm Close Ticket?',
        'error' => 'Unknown error! Please check logs',
    ],
    'logs' => [
        'subscribe' => 'Subscriptions',
        'counts' => '<code>:num</code> records total',
        'rule' => [
            'clear_all' => 'Clear All Records',
            'title' => 'Trigger Records',
            'name' => 'Trigger Rule',
            'reason' => 'Trigger Reason',
            'created_at' => 'Trigger Time',
            'tag' => '✅ Non-permitted access',
            'clear_confirm' => 'Confirm Clear All Records?',
        ],
        'order' => [
            'title' => 'Orders',
            'is_expired' => 'Expired',
            'is_coupon' => 'Used Coupon',
        ],
        'user_traffic' => [
            'title' => 'Data Usage Records',
            'choose_node' => 'Select Node',
        ],
        'user_data_modify_title' => 'Data Change Records',
        'callback' => 'Callback Logs <small>(Payment)</small>',
        'notification' => 'Email Logs',
        'ip_monitor' => 'Online IPs <small>Real-time 2 mins</small>',
        'user_ip' => [
            'title' => 'Online IPs <small>Last 10 mins</small>',
            'connect' => 'Connected IP',
        ],
        'ban' => [
            'title' => 'User Bans',
            'time' => 'Duration',
            'reason' => 'Reason',
            'ban_time' => 'Banned On',
            'last_connect_at' => 'Last Login Time',
        ],
        'credit_title' => 'Balance Change Records',
    ],
    'start_time' => 'Start',
    'end_time' => 'End',
    'goods' => [
        'title' => 'Products',
        'type' => [
            'top_up' => 'Top Up',
            'package' => 'Data Package',
            'plan' => 'Subscription Plan',
        ],
        'info' => [
            'type_hint' => 'Plan affects account expiration, Package only deducts data, does not affect expiration',
            'period_hint' => 'Data allowance resets every N days for plans',
            'limit_num_hint' => 'Max number of purchases per user, 0 for unlimited',
            'available_date_hint' => 'Auto deduct data from total when due',
            'desc_placeholder' => 'Brief description',
            'list_placeholder' => 'Add custom content',
            'list_hint' => 'Start each line with <code><li></code> and end with <code></li></code>',
        ],
        'status' => [
            'yes' => 'On Sale',
            'no' => 'Off Sale',
        ],
        'sell_and_used' => 'Used / Sold',
        'counts' => '<code>:num</code> goods total',
    ],
    'sort_asc' => 'Larger sort value has higher priority',
    'yes' => 'Yes',
    'no' => 'No',
    'rule' => [
        'type' => [
            'reg' => 'Regex',
            'domain' => 'Domain',
            'ip' => 'IP',
            'protocol' => 'Protocol',
        ],
        'counts' => '<code>:num</code> rules total',
        'title' => 'Rules',
        'group' => [
            'type' => [
                'off' => 'Block',
                'on' => 'Allow',
            ],
            'title' => 'Rule Groups',
            'counts' => '<code>:num</code> groups total',
        ],
    ],
    'role' => [
        'name_hint' => 'Unique identifier, e.g. admin',
        'description_hint' => 'Display name, e.g. Administrator',
        'title' => 'Roles',
        'permissions_all' => 'All Permissions',
        'counts' => '<code>:num</code> roles total',
    ],
    'report' => [
        'monthly_accounting' => 'Monthly Accounting',
        'annually_accounting' => 'Annual Accounting',
        'historic_accounting' => 'Historic Accounting',
        'current_month' => 'This Month',
        'last_month' => 'Last Month',
        'current_year' => 'This Year',
        'last_year' => 'Last Year',
        'hourly_traffic' => 'Hourly Traffic',
        'daily_traffic' => 'Daily Traffic',
        'today' => 'Today',
    ],
    'permission' => [
        'title' => 'Permissions',
        'description_hint' => 'Description, e.g. [X system] Edit A',
        'name_hint' => 'Route name, e.g. admin.user.update',
        'counts' => '<code>:num</code> permissions total',
    ],
    'marketing' => [
        'email' => [
            'title' => 'Email Marketing',
            'group_send' => 'Send Email',
            'counts' => '<code>:num</code> emails total',
        ],
        'send_status' => 'Send Status',
        'send_time' => 'Sent On',
        'error_message' => 'Error Messages',
        'push' => [
            'title' => 'Push Notifications',
            'send' => 'Send Notification',
            'counts' => '<code>:num</code> messages total',
        ],
    ],
    'creating' => 'Adding...',
    'article' => [
        'type' => [
            'knowledge' => 'Article',
            'announcement' => 'Announcement',
        ],
        'category_hint' => 'Same category will be grouped together',
        'logo_hint' => 'Recommended size: 100x75',
        'title' => 'Articles',
        'counts' => '<code>:num</code> articles total',
    ],
    'coupon' => [
        'title' => 'Coupons',
        'name_hint' => 'For display',
        'sn_hint' => 'Leave blank for 8-digit random code',
        'type' => [
            'voucher' => 'Voucher',
            'discount' => 'Discount',
            'charge' => 'Recharge',
        ],
        'type_hint' => 'Reduction: deduct amount; Discount: percentage off; Recharge: add amount to balance',
        'value' => '{1} ➖ :num|{2} :num% off|{3} ➕ :num',
        'value_hint' => 'Range 1% ~ 99%',
        'priority_hint' => 'Highest eligible priority coupon used first. Max 255',
        'minimum_hint' => 'Only usable when payment exceeds <strong>:num</strong>',
        'used_hint' => 'Each user can use this <strong>:num</strong> times max',
        'levels_hint' => 'Only usable for selected user levels',
        'groups_hint' => 'Only usable for selected user groups',
        'users_placeholder' => 'Enter user ID, press Enter',
        'user_whitelist_hint' => 'Whitelisted users can use, leave blank if unused',
        'users_blacklist_hint' => 'Blacklisted users cannot use, leave blank if unused',
        'services_placeholder' => 'Enter product ID, press Enter',
        'services_whitelist_hint' => 'Only usable for whitelisted products, leave blank if unused',
        'services_blacklist_hint' => 'Not usable for blacklisted products, leave blank if unused',
        'newbie' => [
            'first_discount' => 'First-time Discount',
            'first_order' => 'First Order',
            'created_days' => 'Account Age',
        ],
        'created_days_hint' => '<code>:day</code> days after registration',
        'limit_hint' => 'Rules have <strong>AND</strong> relation, use properly',
        'info_title' => 'Info',
        'counts' => '<code>:num</code> coupons total',
        'discount' => 'Discount',
        'export_title' => 'Export',
        'single_use' => 'One-time Use',
    ],
    'times' => 'Times',
    'massive_export' => 'Batch Export',
    'system_generate' => 'System Generated',
    'aff' => [
        'rebate_title' => 'Rebate History',
        'counts' => '<code>:num</code> rebates total',
        'title' => 'Withdraw Requests',
        'apply_counts' => '<code>:num</code> requests total',
        'referral' => 'Referral Rebates',
        'commission_title' => 'Request Details',
        'commission_counts' => 'Involves <code>:num</code> orders',
    ],
    'setting' => [
        'common' => [
            'title' => 'General Config',
            'set_default' => 'Set as Default',
            'connect_nodes' => '# of Nodes',
        ],
        'email' => [
            'title' => 'Email Filters <small>(for registration)</small>',
            'tail' => 'Email Suffix',
            'rule' => 'Restriction Type',
            'black' => 'Blacklist',
            'white' => 'Whitelist',
            'tail_placeholder' => 'Enter email suffix',
        ],
        'system' => [
            'title' => 'System Settings',
            'web' => 'General',
            'account' => 'Account',
            'node' => 'Node',
            'extend' => 'Advanced',
            'check_in' => 'Check-in',
            'promotion' => 'Affiliate',
            'notify' => 'Notification',
            'auto_job' => 'Automation',
            'other' => 'Logo|CS|Analytics',
            'payment' => 'Payment',
            'menu' => 'Menu',
        ],
        'no_permission' => 'No permission to change settings!',
    ],
    'system' => [
        'account_expire_notification' => 'Account Expiration Notice',
        'active_times' => 'Max Account Activations',
        'admin_invite_days' => '[Admin] Invitation Expiration',
        'aff_salt' => '[Referral URL] Encrypt User ID',
        'alipay_qrcode' => 'Alipay QR Code',
        'AppStore_id' => '[Apple] Account',
        'AppStore_password' => '[Apple] Password',
        'auto_release_port' => 'Port Recycle',
        'bark_key' => '[Bark] Device Key',
        'captcha_key' => 'Captcha Key',
        'captcha_secret' => 'Captcha Secret/ID',
        'codepay_id' => '[CodePay] ID',
        'codepay_key' => '[CodePay] Key',
        'codepay_url' => '[CodePay] URL',
        'data_anomaly_notification' => 'Data Anomaly Notice',
        'data_exhaust_notification' => 'Data Exhaustion Notice',
        'ddns_key' => '[DNS] Key',
        'ddns_mode' => 'DNS Sync',
        'ddns_secret' => '[DNS] Secret',
        'default_days' => 'Default Account Time',
        'default_traffic' => 'Default Initial Data',
        'detection_check_times' => 'Node Block Alerts',
        'dingTalk_access_token' => '[DingTalk] Access Token',
        'dingTalk_secret' => '[DingTalk] Secret',
        'epay_key' => '[ePay] Key',
        'epay_mch_id' => '[ePay] Merchant ID',
        'epay_url' => '[ePay] URL',
        'expire_days' => 'Expiration Warning',
        'f2fpay_app_id' => '[Alipay] APP ID',
        'f2fpay_private_key' => '[Alipay] Private Key',
        'f2fpay_public_key' => '[Alipay] Public Key',
        'forbid_mode' => 'Access Restriction',
        'invite_num' => 'Default Invitations',
        'is_activate_account' => 'Account Activation',
        'is_AliPay' => 'Alipay',
        'is_ban_status' => 'Expiration Ban',
        'is_captcha' => 'Captcha',
        'is_checkin' => 'Check-in Reward',
        'is_clear_log' => 'Clean Logs',
        'is_custom_subscribe' => 'Advanced Subscription',
        'is_email_filtering' => 'Email Filtering',
        'is_forbid_robot' => 'Forbid Bots',
        'is_free_code' => 'Free Invitation Codes',
        'is_invite_register' => 'Invitation to Register',
        'is_otherPay' => 'Custom Payment',
        'is_QQPay' => 'QQ Pay',
        'is_rand_port' => 'Random Port',
        'is_register' => 'Registration',
        'is_subscribe_ban' => 'Subscription Ban',
        'is_traffic_ban' => 'Data Abuse Ban',
        'is_WeChatPay' => 'WeChat Pay',
        'iYuu_token' => '[IYUU] Token',
        'maintenance_content' => 'Maintenance Notice',
        'maintenance_mode' => 'Maintenance Mode',
        'maintenance_time' => 'Maintenance End',
        'min_port' => 'Port Range',
        'min_rand_traffic' => 'Data Range',
        'node_blocked_notification' => 'Node Blocked Notice',
        'node_daily_notification' => 'Daily Node Report',
        'node_offline_notification' => 'Node Offline Notice',
        'oauth_path' => 'OAuth Platforms',
        'offline_check_times' => 'Offline Notifications',
        'password_reset_notification' => 'Reset Password Notice',
        'paybeaver_app_id' => '[PayBeaver] App ID',
        'paybeaver_app_secret' => '[PayBeaver] App Secret',
        'payjs_key' => '[PayJs] Key',
        'payjs_mch_id' => '[PayJs] Merchant ID',
        'payment_confirm_notification' => 'Manual Payment Confirmation',
        'payment_received_notification' => 'Payment Success Notice',
        'paypal_app_id' => 'App ID',
        'paypal_client_id' => 'Client ID',
        'paypal_client_secret' => 'Client Secret',
        'pushDeer_key' => '[PushDeer] Key',
        'pushplus_token' => '[PushPlus] Token',
        'rand_subscribe' => 'Random Subscription',
        'redirect_url' => 'Redirect URL',
        'referral_money' => 'Min Withdrawal Limit',
        'referral_percent' => 'Rebate Percentage',
        'referral_status' => 'Affiliate',
        'referral_traffic' => 'Registration Bonus',
        'referral_type' => 'Rebate Type',
        'register_ip_limit' => 'Registration IP Limit',
        'reset_password_times' => 'Reset Limit',
        'reset_traffic' => 'Auto Reset Data',
        'server_chan_key' => '[ServerChan] SCKEY',
        'standard_currency' => 'Primary Currency',
        'stripe_public_key' => 'Public Key',
        'stripe_secret_key' => 'Secret Key',
        'stripe_signing_secret' => 'Webhook Secret',
        'subject_name' => 'Custom Product Name',
        'subscribe_ban_times' => 'Subscription Limit',
        'subscribe_domain' => 'Subscription URL',
        'subscribe_max' => 'Max Subscription Nodes',
        'telegram_token' => 'Telegram Token',
        'tg_chat_token' => 'TG Chat Token',
        'theadpay_key' => '[THeadPay] Key',
        'theadpay_mchid' => '[THeadPay] Merchant ID',
        'theadpay_url' => '[THeadPay] URL',
        'ticket_closed_notification' => 'Ticket Closed Notice',
        'ticket_created_notification' => 'Ticket Creation Notice',
        'ticket_replied_notification' => 'Ticket Reply Notice',
        'traffic_ban_time' => 'Ban Duration',
        'traffic_ban_value' => 'Data Abuse Threshold',
        'traffic_limit_time' => 'Check-in Interval',
        'traffic_warning_percent' => 'Data Usage Warning',
        'trojan_license' => 'Trojan License',
        'username_type' => 'Account Username Type',
        'user_invite_days' => '[User] Invitation Expiry',
        'v2ray_license' => 'V2Ray License',
        'v2ray_tls_provider' => 'V2Ray TLS Config',
        'webmaster_email' => 'Admin Email',
        'website_analytics' => 'Analytics Code',
        'website_callback_url' => 'Payment Callback Domain',
        'website_customer_service' => 'CS Code',
        'website_home_logo' => 'Homepage Logo',
        'website_logo' => 'Inner Page Logo',
        'website_name' => 'Site Name',
        'website_security_code' => 'Security Code',
        'website_url' => 'Site Domain',
        'web_api_url' => 'API Domain',
        'wechat_aid' => 'WeChat AID',
        'wechat_cid' => 'WeChat CID',
        'wechat_encodingAESKey' => 'WeChat Encoding Key',
        'wechat_qrcode' => 'WeChat QR Code',
        'wechat_secret' => 'WeChat Secret',
        'wechat_token' => 'WeChat Token',
        'hint' => [
            'account_expire_notification' => 'Notify expiration',
            'active_times' => 'Via email in 24 hours',
            'admin_invite_days' => 'Admin invitation expiration',
            'aff_salt' => 'Encryption salt for referral URL',
            'AppStore_id' => 'Used in articles',
            'AppStore_password' => 'Used in articles',
            'auto_release_port' => 'Auto release port after being banned/expired for <code>'.config('tasks.release_port').'</code> days',
            'bark_key' => 'Device key for iOS push',
            'captcha_key' => 'Browse <a href="https://proxypanel.gitbook.io/wiki/captcha" target="_blank">setup guide</a>',
            'data_anomaly_notification' => 'Notify admin when hourly data exceeds threshold',
            'data_exhaust_notification' => 'Notify when data is running out',
            'ddns_key' => "Browse <a href='https://proxypanel.gitbook.io/wiki/ddns' target='_blank'>setup guide</a>",
            'ddns_mode' => 'Sync domain & IP changes to DNS provider',
            'default_days' => 'Default expiration for new accounts, 0 means expire today',
            'default_traffic' => 'Default data for new accounts',
            'detection_check_times' => 'Auto offline node after N alerts, 0 for unlimited, max 12',
            'dingTalk_access_token' => 'Custom bot <a href=https://open.dingtalk.com/document/group/custom-robot-access#title-jfe-yo9-jl2 target=_blank>access token</a>',
            'dingTalk_secret' => 'Custom bot secret when enabled sign',
            'expire_days' => 'Start account expiration notice',
            'f2fpay_app_id' => 'Alipay APPID',
            'f2fpay_private_key' => 'Alipay private key from secret key tool',
            'f2fpay_public_key' => 'Not the APP public key!',
            'forbid_mode' => 'Block access from specified regions',
            'invite_num' => 'Default invitations per user',
            'is_activate_account' => 'Require activation via email',
            'is_ban_status' => '(Caution) Ban account will reset all user data',
            'is_captcha' => 'Require captcha to login/register if enabled',
            'is_checkin' => 'Random reward when check-in',
            'is_clear_log' => '(Recommended) Auto clean useless/outdated logs when enabled',
            'is_custom_subscribe' => 'Show expiration & data left on subscription list when enabled',
            'is_email_filtering' => 'Blacklist: any other emails; Whitelist: only allowed emails',
            'is_forbid_robot' => 'Return 404 error if accessed by bots/proxies',
            'is_free_code' => 'Hide free invite codes if disabled',
            'is_rand_port' => 'Random port when add/register user',
            'is_register' => 'Disable registration if unchecked',
            'is_subscribe_ban' => 'Auto ban if subscription requests exceed threshold',
            'is_traffic_ban' => 'Auto disable service if data exceeds threshold in 1 hour',
            'iYuu_token' => 'Fill <a href=https://iyuu.cn target=_blank>IYUU token</a> before enabling',
            'maintenance_content' => 'Custom maintenance announcement',
            'maintenance_mode' => "Redirect normal users to maintenance page if enabled| Admin can login via <a href='javascript:(0)'>:url</a>",
            'maintenance_time' => 'For maintenance page countdown',
            'min_port' => 'Port range 1000 - 65535',
            'node_blocked_notification' => 'Detect node block hourly, notify admins',
            'node_daily_notification' => 'Daily node usage report',
            'node_offline_notification' => 'Detect offline every 10 mins, notify if any node is offline',
            'oauth_path' => 'Please enable platforms in .ENV first',
            'offline_check_times' => 'Stop notification after N alerts in 24 hours',
            'password_reset_notification' => 'Allow password reset via email if enabled',
            'paybeaver_app_id' => '<a href="https://merchant.paybeaver.com/" target="_blank">Merchant Center</a> -> Developer -> App ID',
            'paybeaver_app_secret' => '<a href="https://merchant.paybeaver.com/" target="_blank">Merchant Center</a> -> Developer -> App Secret',
            'payjs_mch_id' => 'Get from <a href="https://payjs.cn/dashboard/member" target="_blank">member page</a>',
            'payment_confirm_notification' => 'Notify admin to process manual payment orders',
            'payment_received_notification' => 'Notify user when payment received',
            'pushDeer_key' => 'Fill <a href=https://www.pushdeer.com/official.html target=_blank>PushDeer Push Key</a> before enabling',
            'pushplus_token' => 'Fill <a href=https://www.pushplus.plus/push1.html target=_blank>PushPlus Token</a> before enabling',
            'rand_subscribe' => 'Random order if enabled, otherwise by node list order',
            'redirect_url' => 'Redirect blocked requests to this URL when rules triggered',
            'referral_money' => 'The minimum amount that can be withdrawn',
            'referral_percent' => 'The percentage of order amount the referrer gets',
            'referral_status' => 'Close referral system without affecting existing data',
            'referral_traffic' => 'Give free data traffic when registered via referral',
            'referral_type' => 'New rebates calculated by new mode after switching',
            'register_ip_limit' => 'Number of registrations allowed per IP in 24 hours, 0 for unlimited',
            'reset_password_times' => 'Number of password resets allowed via email in 24 hours',
            'reset_traffic' => 'Automatically reset data based on user plan cycle',
            'server_chan_key' => 'Fill in <a href=https://sc.ftqq.com target=_blank>ServerChan SCKEY</a> before enabling',
            'standard_currency' => 'Primary currency used in panel',
            'subject_name' => 'Custom product name in payment gateways',
            'subscribe_ban_times' => 'Maximum subscription requests allowed per user in 24 hours',
            'subscribe_domain' => 'Start with http:// or https:// to avoid DNS poisoning failure',
            'subscribe_max' => 'Max number of nodes returned in subscription list, 0 for all',
            'telegram_token' => 'Get robot <a href=https://t.me/BotFather target=_blank>TOKEN</a> from @BotFather',
            'tg_chat_token' => 'Fill <a href=https://t.me/realtgchat_bot target=_blank>TG Chat token</a> before enabling',
            'ticket_closed_notification' => 'Notify user when ticket is closed',
            'ticket_created_notification' => 'Notify manager/user depending on creator',
            'ticket_replied_notification' => 'Notify the other party when ticket replied',
            'traffic_ban_time' => 'Duration of auto ban for exceptions',
            'traffic_ban_value' => 'Trigger auto account ban if exceeds this value in 1 hour',
            'traffic_limit_time' => 'Time interval between check-ins',
            'traffic_warning_percent' => 'Send traffic exhaustion notice when daily usage reaches this percentage',
            'username_type' => 'Default username type for users',
            'user_invite_days' => 'Expiration of user-generated invitation codes',
            'v2ray_tls_provider' => 'Node settings override this TLS config',
            'webmaster_email' => 'Contact email shown in some error messages',
            'website_analytics' => 'Analytics JavaScript code',
            'website_callback_url' => 'Prevent payment callback failure due to DNS poisoning',
            'website_customer_service' => 'Customer service JavaScript code',
            'website_name' => 'Website name in emails',
            'website_security_code' => 'Require security code to access site if set',
            'website_url' => 'Main domain used for links',
            'web_api_url' => 'E.g. '.config('app.url'),
            'wechat_aid' => '<a href="https://work.weixin.qq.com/wework_admin/frame#apps" target="_blank">App Management</a> -> AgentId',
            'wechat_cid' => 'Get from <a href="https://work.weixin.qq.com/wework_admin/frame#profile" target="_blank">Enterprise Info</a>',
            'wechat_encodingAESKey' => 'App Management -> App Settings -> EncodingAESKey',
            'wechat_secret' => 'App secret (need enterprise WeChat to view)',
            'wechat_token' => 'App Settings -> TOKEN, callback URL: :url',
        ],
        'placeholder' => [
            'default_url' => 'Default as :url',
            'server_chan_key' => 'Fill ServerChan SCKEY then click Update',
            'pushDeer_key' => 'Fill PushDeer Push Key then click Update',
            'iYuu_token' => 'Fill IYUU token then click Update',
            'bark_key' => 'Fill Bark device key then click Update',
            'telegram_token' => 'Fill Telegram token then click Update',
            'pushplus_token' => 'Please apply at ServerChan',
            'dingTalk_access_token' => 'Custom bot access token',
            'dingTalk_secret' => 'Custom bot secret after signing',
            'wechat_aid' => 'WeChat Enterprise App AID',
            'wechat_cid' => 'Fill WeChat CID then click Update',
            'wechat_secret' => 'WeChat Enterprise App secret',
            'tg_chat_token' => 'Please apply at Telegram',
            'codepay_url' => 'https://codepay.fatq.com/create_order/?',
        ],
        'payment' => [
            'attribute' => 'Payment Gateway',
            'channel' => [
                'alipay' => 'Alipay F2F',
                'codepay' => 'CodePay',
                'epay' => 'ePay',
                'payjs' => 'PayJs',
                'paypal' => 'PayPal',
                'stripe' => 'Stripe',
                'paybeaver' => 'PayBeaver',
                'theadpay' => 'THeadPay',
                'manual' => 'Manual Pay',
            ],
            'hint' => [
                'alipay' => 'This feature requires going to <a href="https://open.alipay.com/platform/appManage.htm?#/create/" target="_blank">Ant Financial Services Open Platform</a> to apply for permission and application',
                'codepay' => 'Please go to <a href="https://codepay.fateqq.com/i/377289" target="_blank">CodePay</a>. Apply for an account, download and set up its software',
                'payjs' => 'Please go to <a href="https://payjs.cn/ref/zgxjnb" target="_blank">PayJs</a> to apply an account',
                'paypal' => 'Login to the <a href="https://www.paypal.com/businessprofile/mytools/apiaccess/firstparty" target="_blank">API credentials application page</a> with your merchant account, agree and get setup information',
                'paybeaver' => 'Please go to <a href="https://merchant.paybeaver.com/?aff_code=iK4GNuX8" target="_blank"> PayBeaver</a> to apply an account',
                'theadpay' => 'Please go to <a href="https://theadpay.com/" target="_blank">THeadPay</a> to request an account',
                'manual' => 'After the gateway is set and selected, it will be displayed on the user-end',
            ],
        ],
        'notification' => [
            'channel' => [
                'telegram' => 'Telegram',
                'wechat' => 'Enterprise WeChat',
                'dingtalk' => 'DingTalk',
                'email' => 'Email',
                'bark' => 'Bark',
                'serverchan' => 'ServerChan',
                'pushdeer' => 'PushDeer',
                'pushplus' => 'PushPlus',
                'iyuu' => 'IYUU',
                'tg_chat' => 'TG Chat',
                'site' => 'Site Popup',
            ],
            'send_test' => 'Send Test Message',
        ],
        'forbid' => [
            'mainland' => 'Forbid Chinese Mainland Access',
            'china' => 'Forbid China Access',
            'oversea' => 'Forbid Oversea Access',
        ],
        'username' => [
            'email' => 'Email',
            'mobile' => 'Phone number',
            'any' => 'Any Username',
        ],
        'active_account' => [
            'before' => 'Pre-registration activation',
            'after' => 'Activate after registration',
        ],
        'ddns' => [
            'namesilo' => 'Namesilo',
            'aliyun' => 'AliCloud/Aliyun',
            'dnspod' => 'DNSPod',
            'cloudflare' => 'CloudFlare',
        ],
        'captcha' => [
            'standard' => 'Standard',
            'geetest' => 'Geetest',
            'recaptcha' => 'Google ReCaptcha',
            'hcaptcha' => 'hCaptcha',
            'turnstile' => 'Turnstile',
        ],
        'referral' => [
            'once' => 'First Purchase Rebate',
            'loop' => 'Always Rebate',
        ],
    ],
    'set_to' => 'Set as :attribute',
    'minute' => 'minutes',
    'query' => 'Query',
    'optional' => 'Optional',
    'require' => 'Required',
];
