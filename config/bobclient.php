<?php
/*
 * ┌─────────────────────────────────────────┐
 * │ BobVPN                                  │
 * ├─────────────────────────────────────────┤
 * │ Copyright © 2021 (https://t.me/Bobs9)   │
 * └─────────────────────────────────────────┘
 */

return [
    // 登录页面配置
    'login'        => [
        'telegram_url'   => '',  // 留空的话则不展示telegram群
        'qq_url'         => '',  // 留空的话则不展示QQ群
        'background_img' => 'https://demo.proxypanel.ml/assets/images/logo64.png', // 背景图片地址,图片宽高不超过 860px * 544px 就行 （留空为默认的背景图）
        'text'           => '一键开启<br>极速上网体验',
        'text_color'     => 'rgba(255, 255, 255, 0.8);',    // 文字和按钮颜色   默认颜色 rgba(255, 255, 255, 0.8);
        'button_color'   => '#8077f1',    // 文字和按钮颜色 默认颜色：#8077f1(v2版本配置)
    ],

    // PC端消息中心图片和跳转链接
    'message'      => [
        'background_img' => 'https://malus.s3cdn.net/uploads/malus_user-guide.jpg', // 背景图片地址
        'url'            => 'https://www.goole.com',    // 跳转链接
    ],

    // Crisp在线客服
    'crisp_enable' => false,  // 是否开启
    'crisp_id'     => '',       // Crisp 的网站ID

    // 弹窗公告
    'notice'       => [
        'is_start' => true, // 是否开启弹窗公告
        'title'    => '最新公告', // 标题
        'content'  => '<strong>这是最新 <i>公告</i> 内容</strong>', // 公告内容，可以为html格式，也可以纯文本
    ],

    // PC端菜单栏显示控制
    'menu'         => [
        'shop' => true,          // 会员
        'user' => true,          // 我的
        'gift' => true,          // 邀请
    ],

    // 检查用户计算机时间
    'check_time'   => [
        'is_check'     => true,  // 是否开启检查
        'differ_time'  => 90,    // 相差多少秒提示
        'warning_text' => '请校准系统时间为北京时间，否则会导致无法上网！', // 提示内容
    ],

    // 个人中心头像
    'user_avatar'  => 'https://demo.proxypanel.ml/assets/images/avatar.svg',
];
