@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/switchery/switchery.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/dropify/dropify.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/toastr/toastr.min.css" rel="stylesheet">
    <style>
        .hr-text::after {
            content: attr(data-content);
            position: absolute;
            top: -0.8em;
            left: 45%;
            transform: translateX(-50%);
            background: #fff;
            padding: 0 15px;
            font-size: 16px;
        }
    </style>
@endsection
@section('content')
    <div class="page-content container-fluid">
        <x-ui.panel :title="trans('admin.menu.setting.system')" icon="fas fa-cog">
            <div class="nav-tabs-horizontal" data-plugin="tabs">
                @php
                    $tabs = [
                        'webSetting' => ['icon' => 'globe', 'text' => trans('admin.setting.system.web')],
                        'account' => ['icon' => 'user-gear', 'text' => trans('admin.setting.system.account')],
                        'node' => ['icon' => 'sitemap', 'text' => trans('admin.setting.system.node')],
                        'security' => ['icon' => 'shield-alt', 'text' => trans('admin.setting.system.security')],
                        'payment' => ['icon' => 'credit-card', 'text' => trans('admin.setting.system.payment')],
                        'notify' => ['icon' => 'bell', 'text' => trans('admin.setting.system.notify')],
                        'automation' => ['icon' => 'robot', 'text' => trans('admin.setting.system.auto_job')],
                    ];
                @endphp

                <ul class="nav nav-tabs" role="tablist">
                    @foreach ($tabs as $id => $tab)
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="tab" href="#{{ $id }}" role="tab"
                               aria-controls="{{ $id }}">
                                <i class="icon fas fa-{{ $tab['icon'] }}" aria-hidden="true"></i>
                                {{ $tab['text'] }}
                            </a>
                        </li>
                    @endforeach
                    <li class="dropdown nav-item" role="presentation">
                        <a class="dropdown-toggle nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
                            {{ trans('admin.setting.system.menu') }}
                        </a>
                        <div class="dropdown-menu" role="menu">
                            @foreach ($tabs as $id => $tab)
                                <a class="dropdown-item {{ $loop->first ? 'active' : '' }}" data-toggle="tab" href="#{{ $id }}" role="tab"
                                   aria-controls="{{ $id }}">
                                    <i class="icon fas fa-{{ $tab['icon'] }}" aria-hidden="true"></i>
                                    {{ $tab['text'] }}
                                </a>
                            @endforeach
                        </div>
                    </li>
                </ul>
                <div class="tab-content py-35 px-35">
                    <!-- 网站常规 -->
                    <x-system.tab-pane id="webSetting" :active="true">
                        <div class="col-12">
                            @if ($errors->any())
                                <x-alert type="danger" :message="$errors->all()" />
                            @endif
                            @if (Session::has('successMsg'))
                                <x-alert :message="Session::pull('successMsg')" />
                            @endif
                        </div>
                        <x-system.input code="website_name" :value="$configs['website_name']" />
                        <x-system.input type="url" code="website_url" :value="$configs['website_url']" />
                        <x-system.select code="standard_currency" :list="array_column(config('common.currency'), 'code', 'name')" />
                        <x-system.input type="email" code="AppStore_id" :value="$configs['AppStore_id']" />
                        <x-system.input type="password" code="AppStore_password" :value="$configs['AppStore_password']" />
                        <x-system.input type="email" code="webmaster_email" :value="$configs['webmaster_email']" />
                        <div class="form-group col-lg-6">
                            <div class="form-row">
                                <label class="col-md-3 col-form-label" for="website_security_code">{{ trans('model.config.website_security_code') }}</label>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input class="form-control" id="website_security_code" type="text" value="{{ $configs['website_security_code'] }}" />
                                        <span class="input-group-append">
                                            <button class="btn btn-info" type="button" onclick="makeWebsiteSecurityCode()">{{ trans('common.generate') }}</button>
                                            <button class="btn btn-primary" type="button"
                                                    onclick="update('website_security_code')">{{ trans('common.update') }}</button>
                                        </span>
                                    </div>
                                    <span class="text-help">{!! trans('admin.system.hint.website_security_code', ['url' => route('login') . '?securityCode=']) !!}</span>
                                </div>
                            </div>
                        </div>
                        <x-system.input type="url" code="website_home_logo" :value="$configs['website_home_logo']" />
                        <x-system.input type="url" code="website_logo" :value="$configs['website_logo']" />
                        <form class="upload-form col-lg-12 row" role="form" action="{{ route('admin.system.extend') }}" method="post"
                              enctype="multipart/form-data">@csrf
                            <x-system.input-file code="website_home_logo" :value="$configs['website_home_logo']" />
                            <x-system.input-file code="website_logo" :value="$configs['website_logo']" />
                        </form>
                        <x-system.textarea code="website_statistics_code" :value="$configs['website_statistics_code']" />
                        <x-system.textarea code="website_customer_service_code" :value="$configs['website_customer_service_code']" />
                    </x-system.tab-pane>
                    <!-- 账号设置 -->
                    <x-system.tab-pane id="account">
                        <x-system.switch code="is_register" :check="$configs['is_register']" />
                        <x-system.select code="oauth_path" multiple :list="array_flip(config('common.oauth.labels'))" />
                        <x-system.select code="username_type" :list="[
                            trans('admin.system.username.email') => 'email',
                            trans('admin.system.username.mobile') => 'numeric',
                            trans('admin.system.username.any') => 'string',
                        ]" />
                        <x-system.select code="is_email_filtering" :list="[trans('common.status.closed') => '', trans('admin.setting.email.black') => 1, trans('admin.setting.email.white') => 2]" />
                        <x-system.select code="is_invite_register" :list="[trans('common.status.closed') => '', trans('admin.optional') => 1, trans('admin.require') => 2]" />
                        <x-system.select code="is_activate_account" :list="[
                            trans('common.status.closed') => '',
                            trans('admin.system.active_account.before') => 1,
                            trans('admin.system.active_account.after') => 2,
                        ]" />
                        <x-system.select code="password_reset_notification" :list="[trans('common.status.closed') => '', trans('admin.system.notification.channel.email') => 'mail']" />
                        <x-system.switch code="is_free_code" :check="$configs['is_free_code']" />
                        <x-system.input code="affiliate_link_salt" :value="$configs['affiliate_link_salt']" />
                        <x-system.switch code="is_rand_port" :check="$configs['is_rand_port']" />
                        <x-system.input-limit code="min_port" hcode="max_port" :value="$configs['min_port']" min="1000" max="$('#max_port').val()" :hvalue="$configs['max_port']"
                                              hmin="$
                            ('#min_port').val()" hmax="65535" />
                        <x-system.input-limit code="default_days" :value="$configs['default_days']" unit="{{ trans_choice('common.days.attribute', 1) }}" />
                        <x-system.input-limit code="default_traffic" :value="$configs['default_traffic']" unit="MB" />
                        <x-system.input-limit code="reset_password_times" :value="$configs['reset_password_times']" />
                        <x-system.input-limit code="active_times" :value="$configs['active_times']" />
                        <x-system.input-limit code="register_ip_limit" :value="$configs['register_ip_limit']" />
                        <x-system.input-limit code="invite_num" :value="$configs['invite_num']" />
                        <x-system.input-limit code="user_invite_days" :value="$configs['user_invite_days']" min="1" unit="{{ trans_choice('common.days.attribute', 1) }}" />
                        <x-system.input-limit code="admin_invite_days" :value="$configs['admin_invite_days']" min="1"
                                              unit="{{ trans_choice('common.days.attribute', 1) }}" />
                        <hr class="col-12 hr-text" data-content="{{ trans('admin.aff.referral') }}" />
                        <x-system.switch code="referral_status" :check="$configs['referral_status']" />
                        <x-system.input-limit code="referral_traffic" :value="$configs['referral_traffic']" unit="MB" />
                        <x-system.select code="referral_reward_type" :list="[
                            trans('common.status.closed') => '',
                            trans('admin.system.referral.once') => 1,
                            trans('admin.system.referral.loop') => 2,
                        ]" />
                        <x-system.input-limit code="referral_percent" :value="$configs['referral_percent'] * 100" max="100" unit="%" />
                        <x-system.input-limit code="referral_money" :value="$configs['referral_money']"
                                              unit="{{ array_column(config('common.currency'), 'symbol', 'code')[sysConfig('standard_currency')] }}" />
                        <hr class="col-12 hr-text" data-content="{{ trans('user.home.attendance.attribute') }}" />
                        <x-system.input-limit code="checkin_interval" :value="$configs['checkin_interval']" unit="{{ ucfirst(trans('validation.attributes.minute')) }}" />
                        <x-system.input-limit code="checkin_reward" hcode="checkin_reward_max" :value="$configs['checkin_reward']" :hvalue="$configs['checkin_reward_max']" :max="$configs['checkin_reward_max']"
                                              :hmin="$configs['checkin_reward']" unit="MB" />
                    </x-system.tab-pane>
                    <!-- 节点设置 -->
                    <x-system.tab-pane id="node">
                        <x-system.input type="url" code="subscribe_domain" :value="$configs['subscribe_domain']" :holder="trans('admin.system.placeholder.default_url', ['url' => $configs['website_url']])" />
                        <x-system.input-limit code="subscribe_max" :value="$configs['subscribe_max']" />
                        <x-system.switch code="rand_subscribe" :check="$configs['rand_subscribe']" />
                        <x-system.switch code="is_custom_subscribe" :check="$configs['is_custom_subscribe']" />
                        <x-system.input type="url" code="web_api_url" :value="$configs['web_api_url']" />
                        <x-system.input code="v2ray_license" :value="$configs['v2ray_license']" />
                        <x-system.input code="trojan_license" :value="$configs['trojan_license']" />
                        <x-system.input code="v2ray_tls_provider" :value="$configs['v2ray_tls_provider']" />
                        <hr class="col-lg-12 hr-text" data-content="{{ trans('model.node.ddns') }}">
                        <x-system.select code="ddns_mode" :list="$ddns_labels" />
                        <x-system.input code="ddns_key" :value="$configs['ddns_key']" />
                        <x-system.input code="ddns_secret" :value="$configs['ddns_secret']" />
                    </x-system.tab-pane>
                    <!-- 安全&验证 -->
                    <x-system.tab-pane id="security">
                        <x-system.select code="forbid_mode" :list="[
                            trans('common.status.closed') => '',
                            trans('admin.system.forbid.mainland') => 'ban_mainland',
                            trans('admin.system.forbid.china') => 'ban_china',
                            trans('admin.system.forbid.oversea') => 'ban_oversea',
                        ]" />
                        <x-system.switch code="is_forbid_robot" :check="$configs['is_forbid_robot']" />
                        <x-system.input type="url" code="redirect_url" :value="$configs['redirect_url']" />
                        <hr class="col-lg-12 hr-text" data-content="{{ trans('auth.captcha.attribute') }}">
                        <x-system.select code="is_captcha" :list="[
                            trans('common.status.closed') => '',
                            trans('admin.system.captcha.standard') => 1,
                            trans('admin.system.captcha.geetest') => 2,
                            trans('admin.system.captcha.recaptcha') => 3,
                            trans('admin.system.captcha.hcaptcha') => 4,
                            trans('admin.system.captcha.turnstile') => 5,
                        ]" />
                        <x-system.input code="captcha_key" :value="$configs['captcha_key']" />
                        <x-system.input code="captcha_secret" :value="$configs['captcha_secret']" />
                        <hr class="col-lg-12 hr-text" data-content="{{ trans('auth.maintenance') }}">
                        <x-system.switch code="maintenance_mode" :check="$configs['maintenance_mode']" :url="route('admin.login')" />
                        <x-system.input type="datetime-local" code="maintenance_time" :value="$configs['maintenance_time']" />
                        <x-system.textarea code="maintenance_content" :value="$configs['maintenance_content']" row="3" />
                    </x-system.tab-pane>
                    <!-- 支付系统 -->
                    <x-system.tab-pane id="payment">
                        <div class="tab-content pb-100 w-p100">
                            <x-system.tab-pane id="paymentSetting" :active="true">
                                <x-system.select code="is_AliPay" :list="[trans('common.status.closed') => '', ...$paymentLists['ali']]" />
                                <x-system.select code="is_QQPay" :list="[trans('common.status.closed') => '', ...$paymentLists['qq']]" />
                                <x-system.select code="is_WeChatPay" :list="[trans('common.status.closed') => '', ...$paymentLists['wechat']]" />
                                <x-system.select code="is_otherPay" multiple :list="$paymentLists['other']" />
                                <x-system.input code="subject_name" :value="$configs['subject_name']" />
                                <x-system.input type="url" code="payment_callback_url" :value="$configs['payment_callback_url']" :holder="trans('admin.system.placeholder.default_url', ['url' => $configs['website_url']])" />
                            </x-system.tab-pane>
                            @foreach ($paymentForms as $code => $forms)
                                <x-system.tab-pane :id="$code">
                                    <div class="form-group col-lg-6 d-flex">
                                        <label class="col-md-3 col-form-label">{{ trans("admin.system.payment.channel.$code") }}</label>
                                        <div class="col-md-9">
                                            {!! $forms['button'] ?? (trans("admin.system.payment.hint.$code") !== "admin.system.payment.hint.$code" ? trans("admin.system.payment.hint.$code") : '') !!}
                                        </div>
                                    </div>
                                    @foreach ($forms['settings'] as $key => $details)
                                        <x-system.input :code="$key" :value="$configs[$key]" :type="$details['type'] ?? null" :holder="$details['holder'] ?? null" />
                                    @endforeach
                                </x-system.tab-pane>
                            @endforeach
                            <x-system.tab-pane id="manual">
                                <div class="form-group col-lg-12 d-flex">
                                    <label class="col-md-3 col-form-label">{{ trans('admin.system.payment.channel.manual') }}</label>
                                    <div class="col-md-7">
                                        {!! trans('admin.system.payment.hint.manual') !!}
                                    </div>
                                </div>
                                @if ($errors->any())
                                    <x-alert class="col-12" :message="$errors->all()" />
                                @endif
                                @if (Session::has('successMsg'))
                                    <x-alert class="col-12" :message="Session::pull('successMsg')" />
                                @endif
                                <x-system.input type="url" code="alipay_qrcode" :value="$configs['alipay_qrcode']" />
                                <x-system.input type="url" code="wechat_qrcode" :value="$configs['wechat_qrcode']" />
                                <form class="upload-form col-lg-12 row" role="form" action="{{ route('admin.system.extend') }}" method="post"
                                      enctype="multipart/form-data">@csrf
                                    <x-system.input-file code="alipay_qrcode" :value="$configs['alipay_qrcode']" />
                                    <x-system.input-file code="wechat_qrcode" :value="$configs['wechat_qrcode']" />
                                </form>
                            </x-system.tab-pane>
                        </div>
                        <ul class="nav nav-tabs nav-tabs-bottom nav-tabs-line dropup" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#paymentSetting" role="tab"
                                   aria-controls="paymentSetting">{{ trans('admin.system.payment.attribute') }}</a>
                            </li>
                            @foreach ($paymentTabs as $tab)
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#{{ $tab }}" role="tab"
                                       aria-controls="{{ $tab }}">{{ trans("admin.system.payment.channel.$tab") }}</a>
                                </li>
                            @endforeach
                        </ul>
                        <ul class="nav nav-tabs nav-tabs-bottom nav-tabs-line dropup" role="tablist">

                            <li class="nav-item dropdown" style="display: none;">
                                <a class="dropdown-toggle nav-link" data-toggle="dropdown" href="#" aria-expanded="false"
                                   aria-haspopup="true">{{ trans('admin.setting.system.menu') }}</a>
                                <div class="dropdown-menu" role="menu">
                                    <a class="dropdown-item active" data-toggle="tab" href="#paymentSetting" role="tab"
                                       aria-controls="paymentSetting">{{ trans('admin.system.payment.attribute') }}</a>
                                    @foreach ($paymentTabs as $tab)
                                        <a class="dropdown-item" data-toggle="tab" href="#{{ $tab }}" role="tab"
                                           aria-controls="{{ $tab }}">{{ trans("admin.system.payment.channel.$tab") }}</a>
                                    @endforeach
                                </div>
                            </li>
                        </ul>
                    </x-system.tab-pane>
                    <!-- 通知系统 -->
                    <x-system.tab-pane id="notify">
                        @foreach ($notifyForms as $code => $config)
                            <x-system.input code="{{ $code }}" :value="$configs[$code]" holder="{{ trans('admin.system.placeholder.' . $code) }}"
                                            :url="$config['url'] ?? null" :test="$config['test'] ?? null" />
                        @endforeach
                        <hr class="col-12 hr-text" data-content="{{ trans('notification.attribute') }}" />
                        <x-system.input-limit code="expire_days" :value="$configs['expire_days']" unit="{{ trans_choice('common.days.attribute', 1) }}" />
                        <x-system.select code="account_expire_notification" multiple :list="[
                            trans('admin.system.notification.channel.email') => 'mail',
                            trans('admin.system.notification.channel.site') => 'database',
                        ]" />
                        <x-system.input-limit code="traffic_warning_percent" :value="$configs['traffic_warning_percent']" unit="%" />
                        <x-system.select code="data_exhaust_notification" multiple :list="[
                            trans('admin.system.notification.channel.email') => 'mail',
                            trans('admin.system.notification.channel.site') => 'database',
                        ]" />
                        <x-system.input-limit code="offline_check_times" :value="$configs['offline_check_times']" unit="{{ trans('admin.times') }}" />
                        <x-system.select code="node_offline_notification" multiple :list="[
                            trans('admin.system.notification.channel.email') => 'mail',
                            trans('admin.system.notification.channel.bark') => 'bark',
                            trans('admin.system.notification.channel.serverchan') => 'serverChan',
                            trans('admin.system.notification.channel.pushdeer') => 'pushDear',
                            trans('admin.system.notification.channel.iyuu') => 'iYuu',
                            trans('admin.system.notification.channel.telegram') => 'telegram',
                            trans('admin.system.notification.channel.dingtalk') => 'dingTalk',
                            trans('admin.system.notification.channel.wechat') => 'weChat',
                            trans('admin.system.notification.channel.tg_chat') => 'tgChat',
                            trans('admin.system.notification.channel.pushplus') => 'pushPlus',
                        ]" />
                        <x-system.input-limit code="detection_check_times" :value="$configs['detection_check_times']" max="12" unit="{{ trans('admin.times') }}" />
                        <x-system.select code="node_blocked_notification" multiple :list="[
                            trans('admin.system.notification.channel.email') => 'mail',
                            trans('admin.system.notification.channel.serverchan') => 'serverChan',
                            trans('admin.system.notification.channel.pushdeer') => 'pushDear',
                            trans('admin.system.notification.channel.iyuu') => 'iYuu',
                            trans('admin.system.notification.channel.telegram') => 'telegram',
                            trans('admin.system.notification.channel.wechat') => 'weChat',
                            trans('admin.system.notification.channel.tg_chat') => 'tgChat',
                            trans('admin.system.notification.channel.pushplus') => 'pushPlus',
                        ]" />
                        <x-system.select code="node_renewal_notification" multiple :list="[
                            trans('admin.system.notification.channel.email') => 'mail',
                            trans('admin.system.notification.channel.bark') => 'bark',
                            trans('admin.system.notification.channel.serverchan') => 'serverChan',
                            trans('admin.system.notification.channel.pushdeer') => 'pushDear',
                            trans('admin.system.notification.channel.iyuu') => 'iYuu',
                            trans('admin.system.notification.channel.telegram') => 'telegram',
                            trans('admin.system.notification.channel.dingtalk') => 'dingTalk',
                            trans('admin.system.notification.channel.wechat') => 'weChat',
                            trans('admin.system.notification.channel.tg_chat') => 'tgChat',
                            trans('admin.system.notification.channel.pushplus') => 'pushPlus',
                            trans('admin.system.notification.channel.site') => 'database',
                        ]" />
                        <x-system.select code="payment_received_notification" multiple :list="[
                            trans('admin.system.notification.channel.email') => 'mail',
                            trans('admin.system.notification.channel.site') => 'database',
                            trans('admin.system.notification.channel.telegram') => 'telegram',
                        ]" />
                        <x-system.select code="payment_confirm_notification" :list="[
                            trans('common.status.closed') => '',
                            trans('admin.system.notification.channel.telegram') => 'telegram',
                            trans('admin.system.notification.channel.dingtalk') => 'dingTalk',
                            trans('admin.system.notification.channel.wechat') => 'weChat',
                        ]" />
                        <x-system.select code="ticket_created_notification" multiple :list="[
                            trans('admin.system.notification.channel.email') => 'mail',
                            trans('admin.system.notification.channel.bark') => 'bark',
                            trans('admin.system.notification.channel.serverchan') => 'serverChan',
                            trans('admin.system.notification.channel.pushdeer') => 'pushDear',
                            trans('admin.system.notification.channel.iyuu') => 'iYuu',
                            trans('admin.system.notification.channel.telegram') => 'telegram',
                            trans('admin.system.notification.channel.dingtalk') => 'dingTalk',
                            trans('admin.system.notification.channel.wechat') => 'weChat',
                            trans('admin.system.notification.channel.tg_chat') => 'tgChat',
                            trans('admin.system.notification.channel.pushplus') => 'pushPlus',
                        ]" />
                        <x-system.select code="ticket_replied_notification" multiple :list="[
                            trans('admin.system.notification.channel.email') => 'mail',
                            trans('admin.system.notification.channel.bark') => 'bark',
                            trans('admin.system.notification.channel.serverchan') => 'serverChan',
                            trans('admin.system.notification.channel.pushdeer') => 'pushDear',
                            trans('admin.system.notification.channel.iyuu') => 'iYuu',
                            trans('admin.system.notification.channel.telegram') => 'telegram',
                            trans('admin.system.notification.channel.dingtalk') => 'dingTalk',
                            trans('admin.system.notification.channel.wechat') => 'weChat',
                            trans('admin.system.notification.channel.tg_chat') => 'tgChat',
                            trans('admin.system.notification.channel.pushplus') => 'pushPlus',
                        ]" />
                        <x-system.select code="ticket_closed_notification" multiple :list="[
                            trans('admin.system.notification.channel.email') => 'mail',
                            trans('admin.system.notification.channel.bark') => 'bark',
                            trans('admin.system.notification.channel.serverchan') => 'serverChan',
                            trans('admin.system.notification.channel.pushdeer') => 'pushDear',
                            trans('admin.system.notification.channel.iyuu') => 'iYuu',
                            trans('admin.system.notification.channel.telegram') => 'telegram',
                            trans('admin.system.notification.channel.dingtalk') => 'dingTalk',
                            trans('admin.system.notification.channel.wechat') => 'weChat',
                            trans('admin.system.notification.channel.tg_chat') => 'tgChat',
                            trans('admin.system.notification.channel.pushplus') => 'pushPlus',
                        ]" />
                    </x-system.tab-pane>
                    <!-- 自动化任务 -->
                    <x-system.tab-pane id="automation">
                        <x-system.switch code="is_clear_log" :check="$configs['is_clear_log']" feature="tasks_clean" />
                        <x-system.input-limit code="tasks_chunk" :value="$configs['tasks_chunk']" :min="100" />
                        <x-system.switch code="reset_traffic" :check="$configs['reset_traffic']" />
                        <x-system.input-limit code="subscribe_rate_limit" :value="$configs['subscribe_rate_limit']" />
                        <x-system.select code="data_anomaly_notification" multiple :list="[
                            trans('admin.system.notification.channel.email') => 'mail',
                            trans('admin.system.notification.channel.bark') => 'bark',
                            trans('admin.system.notification.channel.serverchan') => 'serverChan',
                            trans('admin.system.notification.channel.pushdeer') => 'pushDear',
                            trans('admin.system.notification.channel.iyuu') => 'iYuu',
                            trans('admin.system.notification.channel.telegram') => 'telegram',
                            trans('admin.system.notification.channel.dingtalk') => 'dingTalk',
                            trans('admin.system.notification.channel.wechat') => 'weChat',
                            trans('admin.system.notification.channel.tg_chat') => 'tgChat',
                            trans('admin.system.notification.channel.pushplus') => 'pushPlus',
                        ]" />
                        <x-system.input-limit code="traffic_abuse_limit" :value="$configs['traffic_abuse_limit']" min="1" unit="GB" />
                        <x-system.input-limit code="ban_duration" :value="$configs['ban_duration']" unit="{{ ucfirst(trans('validation.attributes.minute')) }}" />
                        <x-system.input-limit code="auto_release_port" min="0" :value="$configs['auto_release_port']"
                                              unit="{{ ucfirst(trans('validation.attributes.day')) }}" />
                        <x-system.switch code="is_ban_status" :check="$configs['is_ban_status']" />
                        <x-system.select code="node_daily_notification" multiple :list="[
                            trans('admin.system.notification.channel.email') => 'mail',
                            trans('admin.system.notification.channel.serverchan') => 'serverChan',
                            trans('admin.system.notification.channel.pushdeer') => 'pushDear',
                            trans('admin.system.notification.channel.iyuu') => 'iYuu',
                            trans('admin.system.notification.channel.telegram') => 'telegram',
                            trans('admin.system.notification.channel.dingtalk') => 'dingTalk',
                            trans('admin.system.notification.channel.wechat') => 'weChat',
                            trans('admin.system.notification.channel.tg_chat') => 'tgChat',
                            trans('admin.system.notification.channel.pushplus') => 'pushPlus',
                        ]" />
                        <x-system.input-limit code="recently_heartbeat" :value="$configs['recently_heartbeat']" :min="1"
                                              unit="{{ ucfirst(trans('validation.attributes.minute')) }}" />
                        <x-system.task-group type="clean" :items="$configs['tasks_clean']" :units="['minutes', 'hours', 'days', 'months', 'years']" feature="tasks_clean" />
                        <x-system.task-group type="close" :items="$configs['tasks_close']" :units="['minutes', 'hours']" />
                    </x-system.tab-pane>
                </div>
            </div>
        </x-ui.panel>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/lodash/lodash.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/vendor/switchery/switchery.min.js"></script>
    <script src="/assets/global/vendor/dropify/dropify.min.js"></script>
    <script src="/assets/global/vendor/toastr/toastr.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script src="/assets/global/js/Plugin/switchery.js"></script>
    <script src="/assets/global/js/Plugin/responsive-tabs.js"></script>
    <script src="/assets/global/js/Plugin/tabs.js"></script>
    <script src="/assets/global/js/Plugin/toastr.js"></script>
    <script src="/assets/custom/jump-tab.js"></script>
    <script src="/assets/global/js/Plugin/dropify.js"></script>
    <script>
        $(document).ready(function() {
            const selectorValues = {
                forbid_mode: '{{ $configs['forbid_mode'] }}',
                username_type: '{{ $configs['username_type'] ?: 'email' }}',
                is_invite_register: '{{ $configs['is_invite_register'] }}',
                is_activate_account: '{{ $configs['is_activate_account'] }}',
                ddns_mode: '{{ $configs['ddns_mode'] }}',
                is_captcha: '{{ $configs['is_captcha'] }}',
                referral_reward_type: '{{ $configs['referral_reward_type'] }}',
                is_email_filtering: '{{ $configs['is_email_filtering'] }}',
                is_AliPay: '{{ $configs['is_AliPay'] }}',
                is_QQPay: '{{ $configs['is_QQPay'] }}',
                is_WeChatPay: '{{ $configs['is_WeChatPay'] }}',
                is_otherPay: {!! $configs['is_otherPay'] ?: 'null' !!},
                standard_currency: '{{ $configs['standard_currency'] }}',
                oauth_path: {!! $configs['oauth_path'] ?: 'null' !!},
                account_expire_notification: {!! $configs['account_expire_notification'] ?: 'null' !!},
                data_anomaly_notification: {!! $configs['data_anomaly_notification'] ?: 'null' !!},
                data_exhaust_notification: {!! $configs['data_exhaust_notification'] ?: 'null' !!},
                node_blocked_notification: {!! $configs['node_blocked_notification'] ?: 'null' !!},
                node_daily_notification: {!! $configs['node_daily_notification'] ?: 'null' !!},
                node_offline_notification: {!! $configs['node_offline_notification'] ?: 'null' !!},
                node_renewal_notification: {!! $configs['node_renewal_notification'] ?: 'null' !!},
                password_reset_notification: '{{ $configs['password_reset_notification'] }}',
                payment_confirm_notification: '{{ $configs['payment_confirm_notification'] }}',
                payment_received_notification: {!! $configs['payment_received_notification'] ?: 'null' !!},
                ticket_closed_notification: {!! $configs['ticket_closed_notification'] ?: 'null' !!},
                ticket_created_notification: {!! $configs['ticket_created_notification'] ?: 'null' !!},
                ticket_replied_notification: {!! $configs['ticket_replied_notification'] ?: 'null' !!}
            };

            Object.entries(selectorValues).forEach(([selector, value]) => {
                $(`#${selector}`).selectpicker("val", value);
            });

            const disableOptions = (ids, enabledOptions) => {
                ids.forEach(id => {
                    const element = $(`#${id}`);
                    element.find("option").each(function() {
                        optionValue = $(this).val();
                        if (optionValue !== "" && !enabledOptions.includes(optionValue)) {
                            $(this).prop("disabled", true);
                        }
                    });
                    element.selectpicker("refresh");
                });
            };

            disableOptions(["is_AliPay", "is_QQPay", "is_WeChatPay", "is_otherPay"], @json($payments));

            disableOptions(["account_expire_notification", "data_anomaly_notification", "data_exhaust_notification", "node_blocked_notification",
                "node_daily_notification", "node_offline_notification", "node_renewal_notification", "payment_received_notification",
                "ticket_closed_notification", "ticket_created_notification", "ticket_replied_notification"
            ], @json($notifies));

            @if (!$captcha)
                disableOptions(["is_captcha"], ["1"]);
            @endif

        });

        // Feature Tab显示
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll("[data-feature-toggle]").forEach(toggle => {
                const feature = toggle.dataset.featureToggle; // 更简洁的dataset访问方式
                const navItem = document.querySelector(`[data-feature="${feature}"]`);

                // 防御性编程
                if (!navItem) return;

                const handleToggle = (show) => {
                    navItem.classList.toggle("d-none", !show);
                };

                // 初始状态 + 事件监听
                handleToggle(toggle.checked);
                toggle.addEventListener("change", (e) => handleToggle(e.target.checked));
            });
        });

        // 系统设置更新
        const systemUpdate = _.debounce(function(systemItem, value) {
            @can('admin.system.update')
                ajaxPost('{{ route('admin.system.update') }}', {
                    name: systemItem,
                    value: value
                }, function(ret) {
                    if (ret.status === "success") {
                        toastr.success(ret.message, document.querySelector(`label[for="${systemItem}"]`)?.textContent);
                    } else {
                        showMessage({
                            title: ret.message,
                            icon: "error"
                        }, () => window.location.reload());
                    }
                });
            @else
                showMessage({
                    title: '{{ trans('admin.setting.no_permission') }}',
                    icon: "error",
                    timer: 1500
                });
            @endcan
        }, 100);

        // 正常input更新
        const update = systemItem => systemUpdate(systemItem, $(`#${systemItem}`).val());

        // 需要检查限制的更新
        const updateFromInput = (systemItem, lowerBound = null, upperBound = null) => {
            const value = parseInt($(`#${systemItem}`).val());
            let errorMessage = null;

            if (lowerBound !== null && value < lowerBound) {
                errorMessage = `值不能小于 ${lowerBound}`;
            } else if (upperBound !== null && value > upperBound) {
                errorMessage = `值不能大于 ${upperBound}`;
            }

            if (errorMessage) {
                showMessage({
                    title: errorMessage,
                    icon: "warning",
                    timer: 1500
                });
            } else {
                systemUpdate(systemItem, value);
            }
        };

        const updateJson = (key) => {
            const form = document.getElementById(key);
            const inputs = form.querySelectorAll("input, select");
            const temp = {};

            inputs.forEach(input => {
                const [_, name, type] = input.name.split(":");
                if (!temp[name]) {
                    temp[name] = {};
                }
                temp[name][type] = input.value;
            });

            const result = {};
            Object.keys(temp).forEach(name => {
                const val = temp[name].value;
                const unit = temp[name].unit;
                if (val && unit) {
                    result[name] = `-${val} ${unit}`;
                }
            });

            systemUpdate(key, JSON.stringify(result));
        };

        // 其他项更新选择
        const updateFromOther = (inputType, systemItem) => {
            const input = $(`#${systemItem}`);
            let pendingValue = null; // 用于存储待更新的值

            const updateActions = {
                select: () => input.on("changed.bs.select", () => systemUpdate(systemItem, input.val())),
                multiSelect: () => input.on("changed.bs.select", () => {
                    // 存储当前选择的值
                    pendingValue = input.val();
                }).on("hidden.bs.select", () => {
                    // 当 selectpicker 隐藏时进行更新
                    if (pendingValue !== null) {
                        systemUpdate(systemItem, pendingValue);
                        pendingValue = null; // 清除待更新的值
                    }
                }),
                switch: () => systemUpdate(systemItem, document.getElementById(systemItem).checked ? 1 : 0)
            };
            updateActions[inputType] && updateActions[inputType]();
        };

        // 使用通知渠道 发送测试消息
        @can('admin.test.notify')
            function sendTestNotification(channel) {
                ajaxPost('{{ route('admin.test.notify') }}', {
                    channel: channel
                }, function(ret) {
                    if (ret.status === "success") {
                        showMessage({
                            title: ret.message,
                            icon: "success",
                            timer: 1500
                        });
                    } else {
                        showMessage({
                            title: ret.message,
                            icon: "error"
                        });
                    }
                });
            }
        @endcan

        // 生成网站安全码
        function makeWebsiteSecurityCode() {
            ajaxGet('{{ route('createStr') }}', {}, function(securityCode) {
                $("#website_security_code").val(securityCode);
            });
        }

        @can('admin.test.epay')
            function epayInfo() {
                ajaxGet('{{ route('admin.test.epay') }}', function(ret) {
                    if (ret.status === "success") {
                        showMessage({
                            title: "易支付信息(仅供参考)",
                            html: "商户状态: " + ret.data["active"] + " | 账号余额： " + ret.data["money"] + " | 结算账号：" +
                                ret.data["account"] + "<br\><br\>渠道手续费：【支付宝 - " + (100 - ret.data["alirate"]) +
                                "% | 微信 - " + (100 - ret.data["wxrate"]) + "% | QQ钱包 - " + (100 - ret.data["qqrate"]) +
                                "%】<br\><br\> 请按照支付平台的介绍为准，本信息纯粹为Api获取信息",
                            icon: "info"
                        });
                    } else {
                        showMessage({
                            title: ret.message,
                            icon: "error"
                        });
                    }
                });
            }
        @endcan
    </script>
@endsection
