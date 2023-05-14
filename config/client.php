<?php

return [
    'read' => true, // 由数据库接管基础参数, true 为 接管，false为全部使用本文件中的参数；
    // 安卓左侧按钮和跳转地址配置
    'left_button' => [
        'button1' => [
            'text' => '邀请返利',
            'url' => '/user/setting/invite',
            'status' => true,
        ],
        'button2' => [
            'text' => '购买套餐',
            'url' => '/user/shop',
            'status' => true,
        ],
        'button3' => [
            'text' => '官方网站',
            'url' => '/',
            'status' => true,
        ],
    ],

    // 新版安卓端首页两个按钮的显示和跳转
    'android_index_button' => [
        [
            'name' => '商店',
            'url' => '/user/shop',
        ],
        [
            'name' => '官网',
            'url' => '/user',
        ],
    ],

    // .env里面的key值
    'key' => env('APP_KEY'),

    // 站点名称
    'name' => 'Bob`s加速器',

    // 面板地址，最后不要带有 /
    'baseUrl' => 'http://www.xxx.com',

    // API地址
    'subscribe_url' => 'http://api.xxx.com',

    // 签到获得流量
    'checkinMin' => 1, //用户签到最少流量 单位MB
    'checkinMax' => 50, //用户签到最多流量

    'code_payback' => 10, //充值返利百分比
    'invite_gift' => 2, //邀请新用户获得流量奖励，单位G

    // 软件版本和更新地址
    'vpn_update' => [
        'enable' => false,                           //是否开启更新
        'android' => [
            'version' => '2.4.3',                    // 版本号
            'download_url' => 'https://ssr.otakuyun.net/clients/otaku_bob.apk',       //下载地址
            'message' => '版本更新：<br/>1.添加点击签到提示框<br/>2.修复剩余流量显示问题',       //提示信息
            'must' => false,                          //true:强制更新  false:不强制更新
        ],
        'windows' => [
            'version' => '3.7.0',                    // 版本号
            'download_url' => 'https://ssr.otakuyun.net/clients/otaku_bob.exe',       //下载地址
            'message' => '版本更新：<br/>1.修复剩余流量显示问题<br/>2.优化节点测试显示<br/>3.修复弹出网页部分按钮无法使用问题',       //提示信息
            'must' => false,                          //true:强制更新  false:不强制更新
        ],
        'mac' => [
            'version' => '3.7.0',                    // 版本号
            'download_url' => 'https://ssr.otakuyun.net/clients/otaku_bob.zip',       // 下载地址
            'message' => '版本更新：<br/>1.修复剩余流量显示问题<br/>2.优化节点测试显示<br/>3.修复弹出网页部分按钮无法使用问题',       //提示信息
            'must' => false,                          // true:强制更新  false:不强制更新
        ],
    ],

    // Crisp在线客服
    'crisp_enable' => false,  // 是否开启
    'crisp_id' => '2c3c28c2-9265-45ea-8e85-0xxxxx',       // Crisp 的网站ID
    'crisp_logo_url' => 'http://xxxx/vpn/kefu.png',       // Crisp 客服logo

    // 个人中心头像
    'user_avatar' => 'https://ssr.otakuyun.net/assets/images/avatar.svg',

    'show_address' => false, // PC端展示用户IP和地址
    'node_class_name' => [], // 节点的等级对应的名字 格式为 节点等级 => 节点等级名字
    'hidden_node' => [],  // 需要隐藏的节点ID, 数组形式 1,2,3,4
    'login' => [ // 登录页面配置
        'telegram_url' => 'https://t.me/otakucloud',  // 留空的话则不展示telegram群
        'qq_url' => 'https://jq.qq.com/?_wv=1027&k=52AI188',  // 留空的话则不展示QQ群
        'background_img' => 'https://ssr.otakuyun.net/assets/images/logo_1.png', // 背景图片地址,图片宽高不超过 860px * 544px 就行 （留空为默认的背景图）
        'text' => '<p>御宅<br/>飛享<strong style="color:#007bff">雲</strong>端</p>',
        'text_color' => 'rgba(255, 255, 255, 0.8);',    // 文字和按钮颜色   默认颜色 rgba(255, 255, 255, 0.8);
        'button_color' => '#667afa',    // 文字和按钮颜色 默认颜色：#8077f1(v2版本配置)
    ],

    // PC端消息中心图片和跳转链接
    'message' => [
        'background_img' => 'https://malus.s3cdn.net/uploads/malus_user-guide.jpg', // 背景图片地址
        'url' => 'https://www.goole.com',    // 跳转链接
    ],

    // 客户端ping检测 1:中转机 2:落地机
    'ping_test' => 1,

    // 支付
    'payment' => [
        'alipay' => 'theadpay',
        'wechat' => 'paybeaver',
        'default' => 'paybeaver',
        'telegram_admin' => 0,    // 额外的 Telegram 管理员 ID,接收支付提醒
        'paybeaver' => [
            'app_id' => '',
            'app_secret' => '',
            'pay_url' => 'https://api.paybeaver.com',
        ],
        'mgate' => [
            'mgate_api_url' => 'https://api.umipay.net',
            'mgate_app_id' => '',
            'mgate_app_secret' => '',
        ],
        // stripe支付需要https://dashboard.stripe.com/webhooks去配置好webhook
        // 客户端webhook: https://xxxx.com/api/v1/bob/payment/notify
        // 然后复制最新的webhook密钥签名到下面的stripe_webhook
        'stripe' => [
            'stripe_key' => '',
            'stripe_currency' => 'hkd',
            'stripe_webhook' => '',
        ],
        // 平头哥支付
        'theadpay' => [
            'theadpay_url' => 'https://jk.theadpay.com/v1/jk/orders',
            'theadpay_mchid' => '',
            'theadpay_key' => '',
        ],
        // 易支付
        'policepay' => [
            'partner' => '', //商户号
            'key' => '', //商户key
            'sign_type' => strtoupper('MD5'),
            'input_charset' => strtolower('utf-8'),
            'name' => '手抓饼',                  //商品名称，目前无意义
            'transport' => 'https',                   //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
            'appname' => 'PolicePay',           //网站英文名
            'apiurl' => 'https://policepay.cc/',      //支付网关 注意结尾的/符号
            'min_price' => '1',                       //最小支付金额(请填正数)
        ],
        // 当面付
        'facepay' => [
            'alipay_app_id' => '',              //商户号
            'merchant_private_key' => '',
            'alipay_public_key' => '',
        ],
    ],

    // 商城配置
    'shop_plan' => [
        '标准会员' => [1, 2, 3, 4],  //对应商店显示的名称 + [商品ID]
        '高级会员' => [1, 2, 3, 4],  //对应商店显示的名称 + [商品ID]
        '至尊会员' => [1, 2, 3, 4],  //对应商店显示的名称 + [商品ID]
    ],

    // 购买配置
    'enable_bought_reset' => true,  // 购买时是否重置流量
    'enable_bought_extend' => true,  // 购买时是否延长等级期限（同等级配套）

    // 更改订阅方式
    'clash_online_user' => 1,   // 1: 根据在线用户数排序来订阅节点 2: 原版sspanel订阅方式

    // 检查用户计算机时间
    'check_time' => [
        'is_check' => true,  // 是否开启检查
        'differ_time' => 90,    // 相差多少秒提示
        'warning_text' => '请校准系统时间为北京时间，否则会导致无法上网！', // 提示内容
    ],

    // 弹窗公告
    'notice' => [
        'is_start' => true, // 是否开启弹窗公告
        'title' => '最新公告', // 标题
        'content' => '<strong>这是最新 <i>公告</i> 内容</strong>', // 公告内容，可以为html格式，也可以纯文本
    ],

    // 用户登录状态保存天数
    'login_time' => 7,

    // Telegram 机器人
    'enable_telegram' => false,  // 是否开启TG机器人
    'telegram_token' => '',  // Telegram bot,bot 的 token

    // PC端菜单栏显示控制
    'menu' => [
        'shop' => true,          // 会员
        'user' => true,          // 我的
        'gift' => true,          // 邀请
    ],

    // 安卓端商店显示
    'android_shop_show' => true,

    // 注册页发送邮件显示
    'enable_email_verify' => true,

    // 会员即将过期提醒
    'class_expire_notice' => [
        'days' => 7,    // 多天内过期提醒
        'msg' => '您好，系统发现您的账号还剩%s天就过期了，请记得及时续费哦~',  // 过期提醒文字 （%s不要删，这个是替换天数用的）
    ],
];
