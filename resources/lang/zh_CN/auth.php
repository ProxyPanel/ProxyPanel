<?php

declare(strict_types=1);

return [
    'accept_term' => '我已阅读并同意遵守',
    'active' => [
        'attribute' => '激活',
        'error' => [
            'activated' => '该账号已激活，无需再次激活',
            'disable' => '本站关闭了账号激活功能，您可以直接登录！',
            'throttle' => '您已触发激活请求限制，请稍后再尝试。如有问题，请联系:email',
        ],
        'promotion' => '账号尚未激活，请先「:action」！',
        'sent' => '激活链接已发送至您的邮箱，请稍等或检查垃圾箱',
    ],
    'aup' => '可接受使用条款',
    'captcha' => [
        'attribute' => '验证码',
        'error' => [
            'failed' => '验证码错误，请重新输入',
            'timeout' => '验证码已失效，请刷新后重试',
        ],
        'required' => '请正确完成验证码操作',
        'sent' => '验证码已发送至您的邮箱，请稍等或检查垃圾箱',
    ],
    'email' => [
        'error' => [
            'banned' => '本站不支持您使用的邮箱服务商，请更换邮箱',
            'invalid' => '该邮箱不在本站支持的邮箱列表内',
        ],
    ],
    'error' => [
        'account_baned' => '您的账号已被禁用！',
        'login_error' => '登录出现错误，请稍后再试！',
        'login_failed' => '登录失败，请检查账号或密码是否正确！',
        'not_found_user' => '未找到关联的账号，请使用其他方式登录',
        'repeat_request' => '请勿重复请求，请刷新后再试',
        'url_timeout' => '链接已失效，请重新操作',
    ],
    'failed' => '用户名或密码错误。',
    'invite' => [
        'get' => '点击获取邀请码',
        'not_required' => '无需邀请码，可直接注册！',
        'unavailable' => '邀请码不可用，请重试',
    ],
    'login' => '登录',
    'logout' => '登出',
    'maintenance' => '维护',
    'maintenance_tip' => '网站维护中',
    'oauth' => [
        'login_failed' => '第三方登录失败！',
        'register' => '快速注册',
        'registered' => '已注册，请直接登录',
    ],
    'one-click_login' => '一键登录',
    'optional' => '可选',
    'password' => [
        'forget' => '忘记密码？',
        'new' => '输入新密码',
        'original' => '原密码',
        'reset' => [
            'attribute' => '重置密码',
            'error' => [
                'demo' => '演示环境禁止修改管理员密码',
                'disabled' => '本站关闭了密码重置功能，如遇问题请联系:email',
                'same' => '新密码不能与旧密码相同，请重新输入',
                'throttle' => '24小时内只能重设密码:time次，请勿频繁操作',
                'wrong' => '旧密码错误，请重新输入',
            ],
            'sent' => '重置成功，请查看您的邮箱（可能在垃圾箱中）',
            'success' => '新密码设置成功，请前往登录页面',
        ],
    ],
    'register' => [
        'attribute' => '注册',
        'code' => '注册验证码',
        'error' => [
            'disable' => '抱歉，本站暂时关闭注册通道',
            'throttle' => '防刷机制已激活，请勿频繁注册',
        ],
        'failed' => '注册失败，请稍后再试',
        'promotion' => '还没有账号？请先',
    ],
    'remember_me' => '记住我',
    'request' => '获取',
    'throttle' => '您尝试的登录次数过多，请 :seconds 秒后再试。',
    'tos' => '用户条款',
];
