<?php

declare(strict_types=1);

return [
    'accept_term' => '我已阅读并同意遵守',
    'active' => [
        'attribute' => '激活',
        'error' => [
            'activated' => '账号已激活，无需再次激活',
            'disable' => '本站关闭了账号激活子系统，您可以直接去登录！',
            'throttle' => '您已触发本站激活请求限制，请勿频繁操作！如有问题，请联系:email',
        ],
        'promotion' => '账号尚未激活，请先「:action」！',
        'sent' => '激活链接已发送至您的邮箱，请稍作等待或查看垃圾箱',
    ],
    'aup' => '可接受使用条款',
    'captcha' => [
        'attribute' => '验证码',
        'error' => [
            'failed' => '验证码验证失败，请重新输入',
            'timeout' => '验证码不合法！可能已过期，请刷新后重试',
        ],
        'required' => '请正确完成验证码操作',
        'sent' => '验证码已发送至您的邮箱，请稍作等待或查看垃圾箱',
    ],
    'email' => [
        'error' => [
            'banned' => '本站屏蔽了您使用的邮箱服务商，请使用其他有效邮箱',
            'invalid' => '使用邮箱不在本站支持邮箱列表内',
        ],
    ],
    'error' => [
        'account_baned' => '您的账号已被禁止登录！',
        'login_error' => '登录错误，请稍后重试！',
        'login_failed' => '登录失败，请检查邮箱或密码是否输入正确！',
        'not_found_user' => '未找到关联账号，请使用其他方式登录',
        'repeat_request' => '请勿重复请求，请刷新后重试',
        'url_timeout' => '链接已失效，请重新操作',
    ],
    'failed' => '用户名或密码错误。',
    'invite' => [
        'attribute' => '邀请码',
        'error' => [
            'unavailable' => '邀请码不可用，请重试',
        ],
        'get' => '点击获取邀请码',
        'not_required' => '无需邀请码，可直接注册！',
    ],
    'login' => '登录',
    'logout' => '登出',
    'maintenance' => '维护',
    'maintenance_tip' => '网站维护中',
    'oauth' => [
        'bind_failed' => '绑定失败',
        'bind_success' => '绑定成功',
        'login_failed' => '第三方登录失败！',
        'rebind_success' => '重新绑定成功',
        'register' => '快速注册',
        'register_failed' => '注册失败',
        'registered' => '已注册，请直接登录',
        'unbind_failed' => '解绑失败',
        'unbind_success' => '解绑成功',
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
                'disabled' => '本站关闭了密码重置子系统，有事请联系 :email',
                'failed' => '重设密码失败',
                'same' => '新密码不可与旧密码一样，请重新输入',
                'throttle' => '24小时内只能重设密码 :time 次，请勿频繁操作',
                'wrong' => '旧密码错误，请重新输入',
            ],
            'sent' => '重置成功，请查看所用邮箱（邮件可能在垃圾箱中）',
            'success' => '新密码设置成功，请前往登录页面',
        ],
    ],
    'register' => [
        'attribute' => '注册',
        'code' => '注册验证码',
        'error' => [
            'disable' => '抱歉，本站关闭了注册通道',
            'throttle' => '防刷机制已激活，请勿频繁注册',
        ],
        'failed' => '注册失败，请稍后尝试',
        'promotion' => '还没有账号？请去',
        'success' => '注册成功',
    ],
    'remember_me' => '记住我',
    'request' => '获取',
    'throttle' => '您尝试的登录次数过多，请 :seconds 秒后再试。',
    'tos' => '用户条款',
];
