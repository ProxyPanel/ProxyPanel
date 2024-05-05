@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/switchery/switchery.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/dropify/dropify.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h1 class="panel-title">
                    <i class="icon wb-settings" aria-hidden="true"></i>{{ trans('admin.setting.system.title') }}</h1>
            </div>
            <div class="panel-body">
                <div class="nav-tabs-horizontal" data-plugin="tabs">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" data-toggle="tab" href="#webSetting" aria-controls="webSetting" role="tab">{{ trans('admin.setting.system.web') }}</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#account" aria-controls="account" role="tab">{{ trans('admin.setting.system.account') }}</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#node" aria-controls="node" role="tab">{{ trans('admin.setting.system.node') }}</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#extend" aria-controls="extend" role="tab">{{ trans('admin.setting.system.extend') }}</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#checkIn" aria-controls="checkIn" role="tab">{{ trans('admin.setting.system.check_in') }}</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#promo" aria-controls="promo" role="tab">{{ trans('admin.setting.system.promotion') }}</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#notify" aria-controls="notify" role="tab">{{ trans('admin.setting.system.notify') }}</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#auto" aria-controls="auto" role="tab">{{ trans('admin.setting.system.auto_job') }}</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#other" aria-controls="other" role="tab">{{ trans('admin.setting.system.other') }}</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#payment" aria-controls="payment" role="tab">{{ trans('admin.setting.system.payment') }}</a>
                        </li>
                        <li class="dropdown nav-item" role="presentation">
                            <a class="dropdown-toggle nav-link" data-toggle="dropdown" href="#" aria-expanded="false">{{ trans('admin.setting.system.menu') }}</a>
                            <div class="dropdown-menu" role="menu">
                                <a class="dropdown-item active" data-toggle="tab" href="#webSetting" aria-controls="webSetting"
                                   role="tab">{{ trans('admin.setting.system.web') }}</a>
                                <a class="dropdown-item" data-toggle="tab" href="#account" aria-controls="account" role="tab">{{ trans('admin.setting.system.account') }}</a>
                                <a class="dropdown-item" data-toggle="tab" href="#node" aria-controls="node" role="tab">{{ trans('admin.setting.system.node') }}</a>
                                <a class="dropdown-item" data-toggle="tab" href="#extend" aria-controls="extend" role="tab">{{ trans('admin.setting.system.extend') }}</a>
                                <a class="dropdown-item" data-toggle="tab" href="#checkIn" aria-controls="checkIn" role="tab">{{ trans('admin.setting.system.check_in') }}</a>
                                <a class="dropdown-item" data-toggle="tab" href="#promo" aria-controls="promo" role="tab">{{ trans('admin.setting.system.promotion') }}</a>
                                <a class="dropdown-item" data-toggle="tab" href="#notify" aria-controls="notify" role="tab">{{ trans('admin.setting.system.notify') }}</a>
                                <a class="dropdown-item" data-toggle="tab" href="#auto" aria-controls="auto" role="tab">{{ trans('admin.setting.system.auto_job') }}</a>
                                <a class="dropdown-item" data-toggle="tab" href="#other" aria-controls="other" role="tab">{{ trans('admin.setting.system.other') }}</a>
                                <a class="dropdown-item" data-toggle="tab" href="#payment" aria-controls="payment" role="tab">{{ trans('admin.setting.system.payment') }}</a>
                            </div>
                        </li>
                    </ul>
                    <div class="tab-content py-35 px-35">
                        <x-system.tab-pane id="webSetting" :active="true">
                            <x-system.input code="website_name" :value="$website_name"/>
                            <x-system.input code="website_url" :value="$website_url" type="url"/>
                            <x-system.select code="standard_currency" :list="array_column(config('common.currency'), 'code', 'name')"/>
                            <x-system.input code="AppStore_id" :value="$AppStore_id" type="email"/>
                            <x-system.input code="AppStore_password" :value="$AppStore_password" type="password"/>
                            <x-system.input code="webmaster_email" :value="$webmaster_email" type="email"/>
                            <div class="form-group col-lg-6">
                                <div class="form-row">
                                    <label class="col-md-3 col-form-label" for="website_security_code">{{ trans('admin.system.website_security_code') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="website_security_code" value="{{$website_security_code}}"/>
                                            <span class="input-group-append">
                                                <button class="btn btn-info" type="button" onclick="makeWebsiteSecurityCode()">{{ trans('common.generate') }}</button>
                                                <button class="btn btn-primary" type="button" onclick="update('website_security_code')">{{ trans('common.update') }}</button>
                                            </span>
                                        </div>
                                        <span class="text-help">{!! trans('admin.system.hint.website_security_code', ['url' => route('login').'?securityCode=']) !!}</span>
                                    </div>
                                </div>
                            </div>
                            <x-system.select code="forbid_mode"
                                             :list="[trans('common.status.closed') => '', trans('admin.system.forbid.mainland')=> 'ban_mainland', trans('admin.system.forbid.china') => 'ban_china', trans('admin.system.forbid.oversea') => 'ban_oversea']"/>
                            <x-system.switch code="is_forbid_robot" :check="$is_forbid_robot"/>
                            <x-system.switch code="maintenance_mode" :check="$maintenance_mode" :url="route('admin.login')"/>
                            <x-system.input code="maintenance_time" :value="$maintenance_time" type="datetime-local"/>
                            <x-system.textarea code="maintenance_content" :value="$maintenance_content" row="3"/>
                            <x-system.input code="redirect_url" :value="$redirect_url" type="url"/>
                        </x-system.tab-pane>
                        <x-system.tab-pane id="account">
                            <x-system.switch code="is_register" :check="$is_register"/>
                            <x-system.select code="oauth_path" multiple="1" :list="array_flip(config('common.oauth.labels'))"/>
                            <x-system.select code="username_type" :list="[trans('admin.system.username.email') => 'email', trans('admin.system.username.mobile') => 'numeric',
                            trans('admin.system.username.any') => 'string']"/>
                            <x-system.select code="is_invite_register" :list="[trans('common.status.closed') => '', trans('admin.optional') => 1, trans('admin.require') => 2]"/>
                            <x-system.select code="is_activate_account"
                                             :list="[trans('common.status.closed') => '', trans('admin.system.active_account.before') => 1, trans('admin.system.active_account.after') => 2]"/>
                            <x-system.select code="password_reset_notification" :list="[trans('common.status.closed') => '', trans('admin.system.notification.channel.email') =>
                            'mail']"/>
                            <x-system.switch code="is_free_code" :check="$is_free_code"/>
                            <x-system.input code="aff_salt" :value="$aff_salt"/>
                            <x-system.switch code="is_rand_port" :check="$is_rand_port"/>
                            <x-system.input-limit code="min_port" hcode="max_port" :value="$min_port" min="1000" max="$('#max_port').val()" :hvalue="$max_port" hmin="$
                            ('#min_port').val()" hmax="65535"/>
                            <x-system.input-limit code="default_days" :value="$default_days" unit="{{ trans_choice('common.days.attribute', 1) }}"/>
                            <x-system.input-limit code="default_traffic" :value="$default_traffic" unit="MB"/>
                            <x-system.input-limit code="invite_num" :value="$invite_num"/>
                            <x-system.input-limit code="reset_password_times" :value="$reset_password_times"/>
                            <x-system.select code="is_email_filtering"
                                             :list="[trans('common.status.closed') => '', trans('admin.setting.email.black') => 1, trans('admin.setting.email.white') => 2]"/>
                            <x-system.input-limit code="active_times" :value="$active_times"/>
                            <x-system.input-limit code="register_ip_limit" :value="$register_ip_limit"/>
                            <x-system.input-limit code="user_invite_days" :value="$user_invite_days" min="1" unit="{{ trans_choice('common.days.attribute', 1) }}"/>
                            <x-system.input-limit code="admin_invite_days" :value="$admin_invite_days" min="1" unit="{{ trans_choice('common.days.attribute', 1) }}"/>
                        </x-system.tab-pane>
                        <x-system.tab-pane id="node">
                            <x-system.input code="subscribe_domain" :value="$subscribe_domain" :holder="trans('admin.system.placeholder.default_url', ['url' => $website_url])"
                                            type="url"/>
                            <x-system.input-limit code="subscribe_max" :value="$subscribe_max"/>
                            <x-system.switch code="rand_subscribe" :check="$rand_subscribe"/>
                            <x-system.switch code="is_custom_subscribe" :check="$is_custom_subscribe"/>
                            <x-system.input code="web_api_url" :value="$web_api_url" type="url"/>
                            <x-system.input code="v2ray_license" :value="$v2ray_license"/>
                            <x-system.input code="trojan_license" :value="$trojan_license"/>
                            <x-system.input code="v2ray_tls_provider" :value="$v2ray_tls_provider"/>
                        </x-system.tab-pane>
                        <x-system.tab-pane id="extend">
                            <x-system.select code="ddns_mode" :list="$ddns_labels"/>
                            <x-system.input code="ddns_key" :value="$ddns_key"/>
                            <x-system.input code="ddns_secret" :value="$ddns_secret"/>
                            <hr class="col-lg-12">
                            <x-system.select code="is_captcha" :list="[trans('common.status.closed') => '', trans('admin.system.captcha.standard') => 1, trans('admin.system.captcha.geetest') => 2, trans('admin.system.captcha.recaptcha') => 3, trans('admin.system.captcha.hcaptcha') => 4, trans('admin.system.captcha.turnstile') => 5]"/>
                            <x-system.input code="captcha_key" :value="$captcha_key"/>
                            <x-system.input code="captcha_secret" :value="$captcha_secret"/>
                        </x-system.tab-pane>
                        <x-system.tab-pane id="checkIn">
                            <x-system.switch code="is_checkin" :check="$is_checkin"/>
                            <x-system.input-limit code="traffic_limit_time" :value="$traffic_limit_time"/>
                            <x-system.input-limit code="min_rand_traffic" hcode="max_rand_traffic" :value="$min_rand_traffic" :hvalue="$max_rand_traffic" :max="$max_rand_traffic"
                                                  :hmin="$min_rand_traffic" unit="MB"/>
                        </x-system.tab-pane>
                        <x-system.tab-pane id="promo">
                            <x-system.switch code="referral_status" :check="$referral_status"/>
                            <x-system.select code="referral_type"
                                             :list="[trans('common.status.closed') => '', trans('admin.system.referral.once') => 1, trans('admin.system.referral.loop') => 2]"/>
                            <x-system.input-limit code="referral_traffic" :value="$referral_traffic" unit="MB"/>
                            <x-system.input-limit code="referral_percent" :value="$referral_percent * 100" max="100" unit="%"/>
                            <x-system.input-limit code="referral_money" :value="$referral_money"
                                                  unit="{{array_column(config('common.currency'), 'symbol', 'code')[sysConfig('standard_currency')]}}"/>
                        </x-system.tab-pane>
                        <x-system.tab-pane id="notify">
                            <x-system.input-test code="server_chan_key" :value="$server_chan_key" holder="{{ trans('admin.system.placeholder.server_chan_key') }}"
                                                 test="serverChan"/>
                            <x-system.input-test code="pushDeer_key" :value="$pushDeer_key" holder="{{ trans('admin.system.placeholder.pushDeer_key') }}" test="pushDeer"/>
                            <x-system.input-test code="iYuu_token" :value="$iYuu_token" holder="{{ trans('admin.system.placeholder.iYuu_token') }}" test="iYuu"/>
                            <x-system.input-test code="bark_key" :value="$bark_key" holder="{{ trans('admin.system.placeholder.bark_key') }}" test="bark"/>
                            <x-system.input-test code="telegram_token" :value="$telegram_token" holder="{{ trans('admin.system.placeholder.telegram_token') }}" test="telegram"/>
                            <x-system.input-test code="pushplus_token" :value="$pushplus_token" holder="{{ trans('admin.system.placeholder.pushplus_token') }}" test="pushPlus"/>
                            <x-system.input code="dingTalk_access_token" :value="$dingTalk_access_token" holder="{{ trans('admin.system.placeholder.dingTalk_access_token') }}"/>
                            <x-system.input-test code="dingTalk_secret" :value="$dingTalk_secret" holder="{{ trans('admin.system.placeholder.dingTalk_secret') }}" test="dingTalk"/>
                            <x-system.input code="wechat_cid" :value="$wechat_cid" holder="{{ trans('admin.system.placeholder.wechat_cid') }}"/>
                            <x-system.input code="wechat_aid" :value="$wechat_aid" holder="{{ trans('admin.system.placeholder.wechat_aid') }}"/>
                            <x-system.input-test code="wechat_secret" :value="$wechat_secret" holder="{{ trans('admin.system.placeholder.wechat_secret') }}" test="weChat"/>
                            <x-system.input code="wechat_token" :value="$wechat_token" :url="route('wechat.verify')"/>
                            <x-system.input code="wechat_encodingAESKey" :value="$wechat_encodingAESKey"/>
                            <x-system.input-test code="tg_chat_token" :value="$tg_chat_token" holder="{{ trans('admin.system.placeholder.tg_chat_token') }}" test="tgChat"/>
                            <hr class="col-10"/>
                            <x-system.select code="account_expire_notification" multiple="1"
                                             :list="[trans('admin.system.notification.channel.email') => 'mail', trans('admin.system.notification.channel.site') => 'database']"/>
                            <x-system.input-limit code="expire_days" :value="$expire_days" unit="{{ trans_choice('common.days.attribute', 1) }}"/>
                            <x-system.select code="data_exhaust_notification" multiple="1"
                                             :list="[trans('admin.system.notification.channel.email') => 'mail', trans('admin.system.notification.channel.site') => 'database']"/>
                            <x-system.input-limit code="traffic_warning_percent" :value="$traffic_warning_percent" unit="%"/>
                            <x-system.select code="node_offline_notification" multiple="1"
                                             :list="[trans('admin.system.notification.channel.email') => 'mail', trans('admin.system.notification.channel.bark') => 'bark', trans('admin.system.notification.channel.serverchan') => 'serverChan', trans('admin.system.notification.channel.pushdeer') => 'pushDear', trans('admin.system.notification.channel.iyuu') => 'iYuu', trans('admin.system.notification.channel.telegram') => 'telegram', trans('admin.system.notification.channel.dingtalk') => 'dingTalk', trans('admin.system.notification.channel.wechat') => 'weChat', trans('admin.system.notification.channel.tg_chat') => 'tgChat', trans('admin.system.notification.channel.pushplus') => 'pushPlus']"/>
                            <x-system.input-limit code="offline_check_times" :value="$offline_check_times" unit="{{ trans('admin.times') }}"/>
                            <x-system.select code="node_blocked_notification" multiple="1"
                                             :list="[trans('admin.system.notification.channel.email') => 'mail', trans('admin.system.notification.channel.serverchan') => 'serverChan', trans('admin.system.notification.channel.pushdeer') => 'pushDear', trans('admin.system.notification.channel.iyuu') => 'iYuu', trans('admin.system.notification.channel.telegram') => 'telegram', trans('admin.system.notification.channel.wechat') => 'weChat', trans('admin.system.notification.channel.tg_chat') => 'tgChat', trans('admin.system.notification.channel.pushplus') => 'pushPlus']"/>
                            <x-system.input-limit code="detection_check_times" :value="$detection_check_times" max="12" unit="{{ trans('admin.times') }}"/>
                            <x-system.select code="payment_received_notification" multiple="1" :list="[trans('admin.system.notification.channel.email') => 'mail', trans('admin
                            .system.notification.channel.site') => 'database', trans('admin.system.notification.channel.telegram') => 'telegram']"/>
                            <x-system.select code="payment_confirm_notification"
                                             :list="[trans('common.status.closed') => '', trans('admin.system.notification.channel.telegram')=> 'telegram', trans('admin.system.notification.channel.dingtalk') => 'dingTalk', trans('admin.system.notification.channel.wechat') => 'weChat']"/>
                            <x-system.select code="ticket_closed_notification" multiple="1"
                                             :list="[trans('admin.system.notification.channel.email') => 'mail', trans('admin.system.notification.channel.bark') => 'bark', trans('admin.system.notification.channel.serverchan') => 'serverChan', trans('admin.system.notification.channel.pushdeer') => 'pushDear', trans('admin.system.notification.channel.iyuu') => 'iYuu', trans('admin.system.notification.channel.telegram') => 'telegram', trans('admin.system.notification.channel.dingtalk') => 'dingTalk', trans('admin.system.notification.channel.wechat') => 'weChat', trans('admin.system.notification.channel.tg_chat') => 'tgChat', trans('admin.system.notification.channel.pushplus') => 'pushPlus']"/>
                            <x-system.select code="ticket_created_notification" multiple="1"
                                             :list="[trans('admin.system.notification.channel.email') => 'mail', trans('admin.system.notification.channel.bark') => 'bark', trans('admin.system.notification.channel.serverchan') => 'serverChan', trans('admin.system.notification.channel.pushdeer') => 'pushDear', trans('admin.system.notification.channel.iyuu') => 'iYuu', trans('admin.system.notification.channel.telegram') => 'telegram', trans('admin.system.notification.channel.dingtalk') => 'dingTalk', trans('admin.system.notification.channel.wechat') => 'weChat', trans('admin.system.notification.channel.tg_chat') => 'tgChat', trans('admin.system.notification.channel.pushplus') => 'pushPlus']"/>
                            <x-system.select code="ticket_replied_notification" multiple="1"
                                             :list="[trans('admin.system.notification.channel.email') => 'mail', trans('admin.system.notification.channel.bark') => 'bark', trans('admin.system.notification.channel.serverchan') => 'serverChan', trans('admin.system.notification.channel.pushdeer') => 'pushDear', trans('admin.system.notification.channel.iyuu') => 'iYuu', trans('admin.system.notification.channel.telegram') => 'telegram', trans('admin.system.notification.channel.dingtalk') => 'dingTalk', trans('admin.system.notification.channel.wechat') => 'weChat', trans('admin.system.notification.channel.tg_chat') => 'tgChat', trans('admin.system.notification.channel.pushplus') => 'pushPlus']"/>
                        </x-system.tab-pane>
                        <x-system.tab-pane id="auto">
                            <x-system.switch code="is_clear_log" :check="$is_clear_log"/>
                            <x-system.switch code="reset_traffic" :check="$reset_traffic"/>
                            <x-system.switch code="is_subscribe_ban" :check="$is_subscribe_ban"/>
                            <x-system.input-limit code="subscribe_ban_times" :value="$subscribe_ban_times"/>
                            <x-system.switch code="is_traffic_ban" :check="$is_traffic_ban"/>
                            <x-system.select code="data_anomaly_notification" multiple="1"
                                             :list="[trans('admin.system.notification.channel.email') => 'mail', trans('admin.system.notification.channel.bark') => 'bark', trans('admin.system.notification.channel.serverchan') => 'serverChan', trans('admin.system.notification.channel.pushdeer') => 'pushDear', trans('admin.system.notification.channel.iyuu') => 'iYuu', trans('admin.system.notification.channel.telegram') => 'telegram', trans('admin.system.notification.channel.dingtalk') => 'dingTalk', trans('admin.system.notification.channel.wechat') => 'weChat', trans('admin.system.notification.channel.tg_chat') => 'tgChat', trans('admin.system.notification.channel.pushplus') => 'pushPlus']"/>
                            <x-system.input-limit code="traffic_ban_value" :value="$traffic_ban_value" min="1" unit="GB"/>
                            <x-system.input-limit code="traffic_ban_time" :value="$traffic_ban_time" unit="{{ trans('admin.minute') }}"/>
                            <x-system.switch code="auto_release_port" :check="$auto_release_port"/>
                            <x-system.switch code="is_ban_status" :check="$is_ban_status"/>
                            <x-system.select code="node_daily_notification" multiple="1"
                                             :list="[trans('admin.system.notification.channel.email') => 'mail', trans('admin.system.notification.channel.serverchan') => 'serverChan', trans('admin.system.notification.channel.pushdeer') => 'pushDear', trans('admin.system.notification.channel.iyuu') => 'iYuu', trans('admin.system.notification.channel.telegram') => 'telegram', trans('admin.system.notification.channel.dingtalk') => 'dingTalk', trans('admin.system.notification.channel.wechat') => 'weChat', trans('admin.system.notification.channel.tg_chat') => 'tgChat', trans('admin.system.notification.channel.pushplus') => 'pushPlus']"/>
                        </x-system.tab-pane>
                        <x-system.tab-pane id="other">
                            <div class="col-12">
                                @if($errors->any())
                                    <x-alert type="danger" :message="$errors->all()"/>
                                @endif
                                @if (Session::has('successMsg'))
                                    <x-alert type="success" :message="Session::pull('successMsg')"/>
                                @endif
                            </div>
                            <x-system.input code="website_home_logo" :value="$website_home_logo" type="url"/>
                            <x-system.input code="website_logo" :value="$website_logo" type="url"/>
                            <form action="{{route('admin.system.extend')}}" method="post" enctype="multipart/form-data" class="upload-form col-lg-12 row" role="form"
                                  id="setExtend">@csrf
                                <x-system.input-file code="website_home_logo" :value="$website_home_logo"/>
                                <x-system.input-file code="website_logo" :value="$website_logo"/>
                            </form>
                            <x-system.textarea code="website_analytics" :value="$website_analytics"/>
                            <x-system.textarea code="website_customer_service" :value="$website_customer_service"/>
                        </x-system.tab-pane>
                        <div class="tab-pane" id="payment" role="tabpanel">
                            <div class="tab-content pb-100">
                                <x-system.tab-pane id="paymentSetting" :active="true">
                                    <x-system.select code="is_AliPay" :list="[trans('common.status.closed') => '', trans('admin.system.payment.channel.alipay') => 'f2fpay',
                                    trans('admin.system.payment.channel.codepay') => 'codepay', trans('admin.system.payment.channel.epay') => 'epay', trans('admin.system.payment.channel.paybeaver') => 'paybeaver', trans('admin.system.payment.channel.theadpay') => 'theadpay', trans('admin.system.payment.channel.stripe') => 'stripe']"/>
                                    <x-system.select code="is_QQPay" :list="[trans('common.status.closed') => '', trans('admin.system.payment.channel.codepay') => 'codepay',
                                    trans('admin.system.payment.channel.epay') => 'epay']"/>
                                    <x-system.select code="is_WeChatPay" :list="[trans('common.status.closed') => '', trans('admin.system.payment.channel.codepay') => 'codepay',
                                     trans('admin.system.payment.channel.payjs') => 'payjs', trans('admin.system.payment.channel.epay') => 'epay', trans('admin.system.payment.channel.paybeaver') => 'paybeaver', trans('admin.system.payment.channel.stripe') => 'stripe']"/>
                                    <x-system.select code="is_otherPay" multiple="1"
                                                     :list="[trans('admin.system.payment.channel.paypal') => 'paypal', trans('admin.system.payment.channel.stripe') => 'stripe']"/>
                                    <x-system.input code="subject_name" :value="$subject_name"/>
                                    <x-system.input code="website_callback_url" :value="$website_callback_url" :holder="trans('admin.system.placeholder.default_url', ['url' =>
                                    $website_url])" type="url"/>
                                </x-system.tab-pane>
                                <x-system.tab-pane id="AlipayF2F">
                                    <div class="form-group col-lg-6 d-flex">
                                        <label class="col-md-3 col-form-label">{{  trans('admin.system.payment.channel.alipay') }}</label>
                                        <div class="col-md-9">
                                            {!! trans('admin.system.payment.hint.alipay') !!}
                                        </div>
                                    </div>
                                    <x-system.input code="f2fpay_app_id" :value="$f2fpay_app_id"/>
                                    <x-system.input code="f2fpay_private_key" :value="$f2fpay_private_key"/>
                                    <x-system.input code="f2fpay_public_key" :value="$f2fpay_public_key"/>
                                </x-system.tab-pane>
                                <x-system.tab-pane id="CodePay">
                                    <div class="form-group col-lg-6 d-flex">
                                        <label class="col-md-3 col-form-label">{{ trans('admin.system.payment.channel.codepay') }}</label>
                                        <div class="col-md-7">
                                            {!! trans('admin.system.payment.hint.codepay') !!}
                                        </div>
                                    </div>
                                    <x-system.input code="codepay_url" :value="$codepay_url" :holder="trans('admin.system.placeholder.codepay_url')" type="url"/>
                                    <x-system.input code="codepay_id" :value="$codepay_id"/>
                                    <x-system.input code="codepay_key" :value="$codepay_key"/>
                                </x-system.tab-pane>
                                <x-system.tab-pane id="EPay">
                                    <div class="form-group col-lg-6 d-flex">
                                        <label class="col-md-3 col-form-label">{{ trans('admin.system.payment.channel.epay') }}</label>
                                        @can('admin.test.epay')
                                            <div class="col-md-7">
                                                <button class="btn btn-primary" type="button" onclick="epayInfo()">{{ trans('admin.query') }}</button>
                                            </div>
                                        @endcan
                                    </div>
                                    <x-system.input code="epay_url" :value="$epay_url" type="url"/>
                                    <x-system.input code="epay_mch_id" :value="$epay_mch_id"/>
                                    <x-system.input code="epay_key" :value="$epay_key"/>
                                </x-system.tab-pane>
                                <x-system.tab-pane id="PayJs">
                                    <div class="form-group col-lg-6 d-flex">
                                        <label class="col-md-3 col-form-label">{{ trans('admin.system.payment.channel.payjs') }}</label>
                                        <div class="col-md-7">
                                            {!! trans('admin.system.payment.hint.payjs') !!}
                                        </div>
                                    </div>
                                    <x-system.input code="payjs_mch_id" :value="$payjs_mch_id"/>
                                    <x-system.input code="payjs_key" :value="$payjs_key"/>
                                </x-system.tab-pane>
                                <x-system.tab-pane id="PayPal">
                                    <div class="form-group col-lg-6 d-flex">
                                        <label class="col-md-3 col-form-label">{{ trans('admin.system.payment.channel.paypal') }}</label>
                                        <div class="col-md-7">
                                            {!! trans('admin.system.payment.hint.paypal') !!}
                                        </div>
                                    </div>
                                    <x-system.input code="paypal_client_id" :value="$paypal_client_id"/>
                                    <x-system.input code="paypal_client_secret" :value="$paypal_client_secret"/>
                                    <x-system.input code="paypal_app_id" :value="$paypal_app_id"/>
                                </x-system.tab-pane>
                                <x-system.tab-pane id="Stripe">
                                    <div class="form-group col-lg-6 d-flex">
                                        <label class="col-md-3 col-form-label">{{ trans('admin.system.payment.channel.stripe') }}</label>
                                    </div>
                                    <x-system.input code="stripe_public_key" :value="$stripe_public_key"/>
                                    <x-system.input code="stripe_secret_key" :value="$stripe_secret_key"/>
                                    <x-system.input code="stripe_signing_secret" :value="$stripe_signing_secret"/>
                                </x-system.tab-pane>
                                <x-system.tab-pane id="PayBeaver">
                                    <div class="form-group col-lg-6 d-flex">
                                        <label class="col-md-3 col-form-label">{{ trans('admin.system.payment.channel.paybeaver') }}</label>
                                        <div class="col-md-7">
                                            {!! trans('admin.system.payment.hint.paybeaver') !!}
                                        </div>
                                    </div>
                                    <x-system.input code="paybeaver_app_id" :value="$paybeaver_app_id"/>
                                    <x-system.input code="paybeaver_app_secret" :value="$paybeaver_app_secret"/>
                                </x-system.tab-pane>
                                <x-system.tab-pane id="THeadPay">
                                    <div class="form-group col-lg-6 d-flex">
                                        <label class="col-md-3 col-form-label">{{ trans('admin.system.payment.channel.theadpay') }}</label>
                                        <div class="col-md-7">
                                            {!! trans('admin.system.payment.hint.theadpay') !!}
                                        </div>
                                    </div>
                                    <x-system.input code="theadpay_url" :value="$theadpay_url" type="url"/>
                                    <x-system.input code="theadpay_mchid" :value="$theadpay_mchid"/>
                                    <x-system.input code="theadpay_key" :value="$theadpay_key"/>
                                </x-system.tab-pane>
                                <x-system.tab-pane id="Manual">
                                    <div class="form-group col-lg-12 d-flex">
                                        <label class="col-md-3 col-form-label">{{ trans('admin.system.payment.channel.manual') }}</label>
                                        <div class="col-md-7">
                                            {!! trans('admin.system.payment.hint.manual') !!}
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        @if($errors->any())
                                            <x-alert type="danger" :message="$errors->all()"/>
                                        @endif
                                        @if (Session::has('successMsg'))
                                            <x-alert type="success" :message="Session::pull('successMsg')"/>
                                        @endif
                                    </div>
                                    <x-system.input code="alipay_qrcode" :value="$alipay_qrcode" type="url"/>
                                    <x-system.input code="wechat_qrcode" :value="$wechat_qrcode" type="url"/>
                                    <form action="{{route('admin.system.extend')}}" method="post" enctype="multipart/form-data" class="upload-form col-lg-12 row" role="form"
                                          id="setExtend">@csrf
                                        <x-system.input-file code="alipay_qrcode" :value="$alipay_qrcode"/>
                                        <x-system.input-file code="wechat_qrcode" :value="$wechat_qrcode"/>
                                    </form>
                                </x-system.tab-pane>
                            </div>
                            <ul class="nav nav-tabs nav-tabs-bottom nav-tabs-line dropup" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#paymentSetting" aria-controls="paymentSetting"
                                       role="tab">{{ trans('admin.system.payment.attribute') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#AlipayF2F" aria-controls="AlipayF2F"
                                       role="tab">{{ trans('admin.system.payment.channel.alipay') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#CodePay" aria-controls="CodePay" role="tab">{{ trans('admin.system.payment.channel.codepay') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#EPay" aria-controls="EPay" role="tab">{{ trans('admin.system.payment.channel.epay') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#PayJs" aria-controls="PayJs" role="tab">{{ trans('admin.system.payment.channel.payjs') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#PayPal" aria-controls="PayPal" role="tab">{{ trans('admin.system.payment.channel.paypal') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#Stripe" aria-controls="Stripe" role="tab">{{ trans('admin.system.payment.channel.stripe') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#PayBeaver" aria-controls="PayBeaver"
                                       role="tab">{{ trans('admin.system.payment.channel.paybeaver') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#THeadPay" aria-controls="THeadPay"
                                       role="tab">{{ trans('admin.system.payment.channel.theadpay') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#Manual" aria-controls="Manual" role="tab">{{ trans('admin.system.payment.channel.manual') }}</a>
                                </li>
                                <li class="nav-item dropdown" style="display: none;">
                                    <a class="dropdown-toggle nav-link" data-toggle="dropdown" href="#" aria-expanded="false"
                                       aria-haspopup="true">{{ trans('admin.setting.system.menu') }}</a>
                                    <div class="dropdown-menu" role="menu">
                                        <a class="dropdown-item active" data-toggle="tab" href="#paymentSetting" aria-controls="paymentSetting"
                                           role="tab">{{ trans('admin.system.payment.attribute') }}</a>
                                        <a class="dropdown-item" data-toggle="tab" href="#AlipayF2F" aria-controls="AlipayF2F"
                                           role="tab">{{ trans('admin.system.payment.channel.alipay') }}</a>
                                        <a class="dropdown-item" data-toggle="tab" href="#CodePay" aria-controls="CodePay"
                                           role="tab">{{ trans('admin.system.payment.channel.codepay') }}</a>
                                        <a class="dropdown-item" data-toggle="tab" href="#EPay" aria-controls="EPay" role="tab">{{ trans('admin.system.payment.channel.epay') }}</a>
                                        <a class="dropdown-item" data-toggle="tab" href="#PayJs" aria-controls="PayJs"
                                           role="tab">{{ trans('admin.system.payment.channel.payjs') }}</a>
                                        <a class="dropdown-item" data-toggle="tab" href="#PayPal" aria-controls="PayPal"
                                           role="tab">{{ trans('admin.system.payment.channel.paypal') }}</a>
                                        <a class="dropdown-item" data-toggle="tab" href="#Stripe" aria-controls="Stripe"
                                           role="tab">{{ trans('admin.system.payment.channel.stripe') }}</a>
                                        <a class="dropdown-item" data-toggle="tab" href="#PayBeaver" aria-controls="PayBeaver"
                                           role="tab">{{ trans('admin.system.payment.channel.paybeaver') }}</a>
                                        <a class="dropdown-item" data-toggle="tab" href="#THeadPay" aria-controls="THeadPay"
                                           role="tab">{{ trans('admin.system.payment.channel.theadpay') }}</a>
                                        <a class="dropdown-item" data-toggle="tab" href="#Manual" aria-controls="Manual"
                                           role="tab">{{ trans('admin.system.payment.channel.manual') }}</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/vendor/switchery/switchery.min.js"></script>
    <script src="/assets/global/vendor/dropify/dropify.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script src="/assets/global/js/Plugin/switchery.js"></script>
    <script src="/assets/global/js/Plugin/responsive-tabs.js"></script>
    <script src="/assets/global/js/Plugin/tabs.js"></script>
    <script src="/assets/custom/jump-tab.js"></script>
    <script src="/assets/global/js/Plugin/dropify.js"></script>
    <script>
      $(document).ready(function() {
        $('#forbid_mode').selectpicker('val', '{{$forbid_mode}}');
        $('#username_type').selectpicker('val', '{{$username_type ?? 'email'}}');
        $('#is_invite_register').selectpicker('val', '{{$is_invite_register}}');
        $('#is_activate_account').selectpicker('val', '{{$is_activate_account}}');
        $('#ddns_mode').selectpicker('val', '{{$ddns_mode}}');
        $('#is_captcha').selectpicker('val', '{{$is_captcha}}');
        $('#referral_type').selectpicker('val', '{{$referral_type}}');
        $('#is_email_filtering').selectpicker('val', '{{$is_email_filtering}}');
        $('#is_AliPay').selectpicker('val', '{{$is_AliPay}}');
        $('#is_QQPay').selectpicker('val', '{{$is_QQPay}}');
        $('#is_WeChatPay').selectpicker('val', '{{$is_WeChatPay}}');
        $('#standard_currency').selectpicker('val', '{{$standard_currency}}');
        $('#is_otherPay').selectpicker('val', {!! $is_otherPay !!});
        $('#oauth_path').selectpicker('val', {!! $oauth_path !!});
        $('#account_expire_notification').selectpicker('val', {!! $account_expire_notification !!});
        $('#data_anomaly_notification').selectpicker('val', {!! $data_anomaly_notification !!});
        $('#data_exhaust_notification').selectpicker('val', {!! $data_exhaust_notification !!});
        $('#node_blocked_notification').selectpicker('val', {!! $node_blocked_notification !!});
        $('#node_daily_notification').selectpicker('val', {!! $node_daily_notification !!});
        $('#node_offline_notification').selectpicker('val', {!! $node_offline_notification !!});
        $('#password_reset_notification').selectpicker('val', '{{$password_reset_notification}}');
        $('#payment_confirm_notification').selectpicker('val', '{{$payment_confirm_notification}}');
        $('#payment_received_notification').selectpicker('val', {!! $payment_received_notification !!});
        $('#ticket_closed_notification').selectpicker('val', {!! $ticket_closed_notification !!});
        $('#ticket_created_notification').selectpicker('val', {!! $ticket_created_notification !!});
        $('#ticket_replied_notification').selectpicker('val', {!! $ticket_replied_notification !!});

        // Get all options within select
        disablePayment(document.getElementById('is_AliPay').getElementsByTagName('option'));
        disablePayment(document.getElementById('is_QQPay').getElementsByTagName('option'));
        disablePayment(document.getElementById('is_WeChatPay').getElementsByTagName('option'));
        disablePayment(document.getElementById('is_otherPay').getElementsByTagName('option'));

          @if (!$captcha)
          disableCaptcha(document.getElementById('is_captcha').getElementsByTagName('option'));
          @endif

      });

      function disablePayment(op) {
        for (let i = 1; i < op.length; i++) {
            @json($payments).
          includes(op[i].value) ? op[i].disabled = false : op[i].disabled = true;
        }
      }

      function disableCaptcha(op) {
        for (let i = 2; i < op.length; i++) {
          op[i].disabled = true;
        }
      }

      // 系统设置更新
      function systemUpdate(systemItem, value) {
          @can('admin.system.update')
          $.post('{{route('admin.system.update')}}', {
            _token: '{{csrf_token()}}',
            name: systemItem,
            value: value,
          }, function(ret) {
            if (ret.status === 'success') {
              swal.fire({title: ret.message, icon: 'success', timer: 1500, showConfirmButton: false});
            } else {
              swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
            }
          });
          @else
          swal.fire({
            title: '{{ trans('admin.setting.no_permission') }}',
            icon: 'error',
            timer: 1500,
            showConfirmButton: false,
          });
          @endcan
      }

      // 正常input更新
      function update(systemItem) {
        systemUpdate(systemItem, $('#' + systemItem).val());
      }

      // 需要检查限制的更新
      function updateFromInput(systemItem, lowerBound = false, upperBound = false) {
        let value = parseInt($('#' + systemItem).val());
        if (lowerBound !== false && value < lowerBound) {
          swal.fire({title: '不能小于' + lowerBound, icon: 'warning', timer: 1500, showConfirmButton: false});
        } else if (upperBound !== false && value > upperBound) {
          swal.fire({title: '不能大于' + upperBound, icon: 'warning', timer: 1500, showConfirmButton: false});
        } else {
          systemUpdate(systemItem, value);
        }
      }

      // 其他项更新选择
      function updateFromOther(inputType, systemItem) {
        let input = $('#' + systemItem);
        switch (inputType) {
          case 'select':
            input.on('changed.bs.select', function() {
              systemUpdate(systemItem, $(this).val());
            });
            break;
          case 'multiSelect':
            input.on('changed.bs.select', function() {
              systemUpdate(systemItem, $(this).val().join(','));
            });
            break;
          case 'switch':
            systemUpdate(systemItem, document.getElementById(systemItem).checked ? 1 : 0);
            break;
          default:
            break;
        }
      }

      // 使用通知渠道 发送测试消息
      @can('admin.test.notify')
      function sendTestNotification(channel) {
        $.post('{{route('admin.test.notify')}}', {_token: '{{csrf_token()}}', channel: channel}, function(ret) {
          if (ret.status === 'success') {
            swal.fire({title: ret.message, icon: 'success', timer: 1500, showConfirmButton: false});
          } else {
            swal.fire({title: ret.message, icon: 'error'});
          }
        });
      }
      @endcan

      // 生成网站安全码
      function makeWebsiteSecurityCode() {
        $.get('{{route('createStr')}}', function(ret) {
          $('#website_security_code').val(ret);
        });
      }

      @can('admin.test.epay')
      function epayInfo() {
        $.get('{{route('admin.test.epay')}}', function(ret) {
          if (ret.status === 'success') {
            swal.fire({
              title: '易支付信息(仅供参考)',
              html: '商户状态: ' + ret.data['active'] + ' | 账号余额： ' + ret.data['money'] + ' | 结算账号：' +
                  ret.data['account'] + '<br\><br\>渠道手续费：【支付宝 - ' + (100 - ret.data['alirate']) +
                  '% | 微信 - ' + (100 - ret.data['wxrate']) + '% | QQ钱包 - ' + (100 - ret.data['qqrate']) +
                  '%】<br\><br\> 请按照支付平台的介绍为准，本信息纯粹为Api获取信息',
              icon: 'info',
            });
          } else {
            swal.fire({title: ret.message, icon: 'error'});
          }
        });
      }
        @endcan
    </script>
@endsection
