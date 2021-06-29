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
                <h1 class="panel-title"><i class="icon wb-settings"></i>通用配置</h1>
            </div>
            <div class="panel-body">
                <div class="nav-tabs-horizontal" data-plugin="tabs">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" data-toggle="tab" href="#webSetting" aria-controls="webSetting" role="tab">网站常规</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#account" aria-controls="account" role="tab">账号设置</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#node" aria-controls="node" role="tab">节点设置</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#extend" aria-controls="extend" role="tab">拓展功能</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#checkIn" aria-controls="checkIn" role="tab">签到系统</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#promo" aria-controls="promo" role="tab">推广系统</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#notify" aria-controls="notify" role="tab">通知系统</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#auto" aria-controls="auto" role="tab">自动任务</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#other" aria-controls="other" role="tab">LOGO|客服|统计</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#payment" aria-controls="payment" role="tab">支付系统</a>
                        </li>
                        <li class="dropdown nav-item" role="presentation">
                            <a class="dropdown-toggle nav-link" data-toggle="dropdown" href="#" aria-expanded="false">菜单</a>
                            <div class="dropdown-menu" role="menu">
                                <a class="dropdown-item active" data-toggle="tab" href="#webSetting" aria-controls="webSetting" role="tab">网站常规</a>
                                <a class="dropdown-item" data-toggle="tab" href="#account" aria-controls="account" role="tab">账号设置</a>
                                <a class="dropdown-item" data-toggle="tab" href="#node" aria-controls="node" role="tab">节点设置</a>
                                <a class="dropdown-item" data-toggle="tab" href="#extend" aria-controls="extend" role="tab">拓展功能</a>
                                <a class="dropdown-item" data-toggle="tab" href="#checkIn" aria-controls="checkIn" role="tab">签到系统</a>
                                <a class="dropdown-item" data-toggle="tab" href="#promo" aria-controls="promo" role="tab">推广系统</a>
                                <a class="dropdown-item" data-toggle="tab" href="#notify" aria-controls="notify" role="tab">通知系统</a>
                                <a class="dropdown-item" data-toggle="tab" href="#auto" aria-controls="auto" role="tab">自动任务</a>
                                <a class="dropdown-item" data-toggle="tab" href="#other" aria-controls="other" role="tab">LOGO|客服|统计</a>
                                <a class="dropdown-item" data-toggle="tab" href="#payment" aria-controls="payment" role="tab">支付系统</a>
                            </div>
                        </li>
                    </ul>
                    <div class="tab-content py-35 px-35">
                        <x-system.tab-pane id="webSetting" :active="true">
                            <x-system.input title="网站名称" :value="$website_name" code="website_name" help="发邮件时展示"/>
                            <x-system.input title="网站地址" :value="$website_url" code="website_url" help="生成重置密码、在线支付必备" type="url"/>
                            <x-system.input title="苹果账号" :value="$AppStore_id" code="AppStore_id" help="iOS软件设置教程中使用的苹果账号" type="email"/>
                            <x-system.input title="苹果密码" :value="$AppStore_password" code="AppStore_password" help="iOS软件设置教程中使用的苹果密码" type="password"/>
                            <x-system.input title="管理员邮箱" :value="$webmaster_email" code="webmaster_email" help="错误提示时会提供管理员邮箱作为联系方式" type="email"/>
                            <div class="form-group col-lg-6">
                                <div class="form-row">
                                    <label class="col-md-3 col-form-label" for="website_security_code">网站安全码</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="website_security_code" value="{{$website_security_code}}"/>
                                            <span class="input-group-append">
                                                <button class="btn btn-info" type="button" onclick="makeWebsiteSecurityCode()">生成</button>
                                                <button class="btn btn-primary" type="button" onclick="update('website_security_code')">{{trans('common.update')}}</button>
                                            </span>
                                        </div>
                                        <span class="text-help">非空时必须通过<a href="{{route('login')}}?securityCode=" target="_blank">安全入口</a>加上安全码才可访问</span>
                                    </div>
                                </div>
                            </div>
                            <x-system.select title="禁止访问模式" code="forbid_mode" :list="['关闭' => '', '阻拦大陆'=> 'ban_mainland', '阻拦中国' => 'ban_china', '阻拦海外' => 'ban_oversea']"
                                             help="依据IP对对应地区进行阻拦，非阻拦地区可正常访问"/>
                            <x-system.switch title="阻止机器人访问" code="is_forbid_robot" :check="$is_forbid_robot" help="如果是机器人、爬虫、代理访问网站则会抛出404错误"/>
                            <x-system.switch title="维护模式" code="maintenance_mode" :check="$maintenance_mode"
                                             help="启用后，用户访问转移至维护界面 | 管理员使用 <a href='javascript:(0)'>{{route('admin.login')}}</a> 登录"/>
                            <x-system.input title="维护结束时间" :value="$maintenance_time" code="maintenance_time" help="用于维护界面倒计时" type="datetime-local"/>
                            <x-system.textarea title="维护介绍内容" code="maintenance_content" :value="$maintenance_content" row="3" help="自定义维护内容信息"/>
                            <x-system.input title="重定向地址" :value="$redirect_url" code="redirect_url" help="触发审计规则时访问请求被阻断并重定向至该地址" type="url"/>
                        </x-system.tab-pane>
                        <x-system.tab-pane id="account">
                            <x-system.switch title="用户注册" code="is_register" :check="$is_register" help="关闭后无法注册"/>
                            <x-system.select title="邀请注册" code="is_invite_register" :list="['关闭' => '', '可选'=> 1, '必须' => 2]"/>
                            <x-system.select title="激活账号" code="is_activate_account" :list="['关闭' => '', '注册前激活'=> 1, '注册后激活' => 2]" help="启用后用户需要通过邮件来激活账号"/>
                            <x-system.select title="重置密码" code="password_reset_notification" :list="['关闭' => '', '邮箱'=> 'mail']" help="启用后用户可以重置密码"/>
                            <x-system.switch title="免费邀请码" code="is_free_code" :check="$is_free_code" help="关闭后免费邀请码不可见"/>
                            <x-system.input title="邀请链接 用户信息字符化" :value="$aff_salt" code="aff_salt" help="留空时，邀请链接将显示用户ID；填入任意英文/数字 即可对用户链接ID进行加密"/>
                            <x-system.switch title="随机端口" code="is_rand_port" :check="$is_rand_port" help="注册、添加用户时随机生成端口"/>
                            <x-system.input-limit title="端口范围" code="min_port" hcode="max_port" :value="$min_port" min="1000" max="$('#max_port').val()"
                                                  :hvalue="$max_port" hmin="$('#min_port').val()" hmax="65535" help="端口范围：1000 - 65535"/>
                            <x-system.input-limit title="初始有效期" code="default_days" :value="$default_days" unit="天" help="用户注册时默认账户有效期，为0即当天到期"/>
                            <x-system.input-limit title="初始流量" code="default_traffic" :value="$default_traffic" unit="MB" help="用户注册时默认可用流量"/>
                            <x-system.input-limit title="可生成邀请码数" code="invite_num" :value="$invite_num" help="用户可以生成的邀请码数"/>
                            <x-system.input-limit title="重置密码次数" code="reset_password_times" :value="$reset_password_times" help="24小时内可以通过邮件重置密码次数"/>
                            <x-system.select title="邮箱过滤机制" code="is_email_filtering" :list="['关闭' => '', '黑名单' => 1, '白名单' => 2]"
                                             help="黑名单: 用户可使用任意黑名单外的邮箱注册；白名单:用户只能选择使用白名单中的邮箱后缀注册"/>
                            <x-system.input-limit title="激活账号次数" code="active_times" :value="$active_times" help="24小时内可以通过邮件激活账号次数"/>
                            <x-system.input-limit title="同IP注册限制" code="register_ip_limit" :value="$register_ip_limit" help="同IP在24小时内允许注册数量，为0时不限制"/>
                            <x-system.input-limit title="用户-邀请码有效期" code="user_invite_days" :value="$user_invite_days" min="1" unit="天" help="用户自行生成邀请的有效期"/>
                            <x-system.input-limit title="管理员-邀请码有效期" code="admin_invite_days" :value="$admin_invite_days" min="1" unit="天" help="管理员生成邀请码的有效期"/>
                        </x-system.tab-pane>
                        <x-system.tab-pane id="node">
                            <x-system.input title="节点订阅地址" :value="$subscribe_domain" code="subscribe_domain" help="（推荐）防止面板域名被DNS投毒后无法正常订阅，需带http://或https://"
                                            :holder="'默认为 '.$website_url" type="url"/>
                            <x-system.input-limit title="订阅节点数" code="subscribe_max" :value="$subscribe_max" help="客户端订阅时取得几个节点，为0时返回全部节点"/>
                            <x-system.switch title="随机订阅" code="rand_subscribe" :check="$rand_subscribe" help="启用后，订阅时将随机返回节点信息，否则按节点排序返回"/>
                            <x-system.switch title="高级订阅" code="is_custom_subscribe" :check="$is_custom_subscribe" help="启用后，订阅信息顶部将显示过期时间、剩余流量（只支持个别客户端）"/>
                            <x-system.input title="授权/后端访问域名" :value="$web_api_url" code="web_api_url" help="例：https://demo.proxypanel.ml" type="url"/>
                            <x-system.input title="V2Ray授权" :value="$v2ray_license" code="v2ray_license"/>
                            <x-system.input title="Trojan授权" :value="$trojan_license" code="trojan_license"/>
                            <x-system.input title="V2Ray TLS配置" :value="$v2ray_tls_provider" code="v2ray_tls_provider" help="后端自动签发/载入TLS证书时用（节点的设置值优先级高于此处）"/>
                        </x-system.tab-pane>
                        <x-system.tab-pane id="extend">
                            <x-system.select title="DDNS模式" code="ddns_mode"
                                             :list="['关闭' => '', 'Namesilo' => 'namesilo', '阿里云(国际&国内)' => 'aliyun', 'DNSPod' => 'dnspod', 'CloudFlare' => 'cloudflare']"
                                             help="添加/编辑/删除节点的【域名、ipv4、ipv6】时，自动更新对应内容至DNS服务商"/>
                            <x-system.input title="DNS服务商Key" :value="$ddns_key" code="ddns_key"
                                            help="浏览<a href='https://proxypanel.gitbook.io/wiki/ddns' target='_blank'>设置指南</a>来设置"/>
                            <x-system.input title="DNS服务商Secret" :value="$ddns_secret" code="ddns_secret"/>
                            <hr class="col-lg-12">
                            <x-system.select title="验证码模式" code="is_captcha"
                                             :list="['关闭' => '', '普通验证码' => 1, '极验Geetest' => 2, 'Google reCaptcha' => 3, 'hCaptcha' => 4]" help="启用后 登录/注册 需要进行验证码认证"/>
                            <x-system.input title="验证码 Key" :value="$captcha_key" code="captcha_key"
                                            help='浏览<a href="https://proxypanel.gitbook.io/wiki/captcha" target="_blank">设置指南</a>来设置'/>
                            <x-system.input title="验证码 Secret/ID" :value="$captcha_secret" code="captcha_secret"/>
                        </x-system.tab-pane>
                        <x-system.tab-pane id="checkIn">
                            <x-system.switch title="签到加流量" code="is_checkin" :check="$is_checkin" help="登录时将根据流量范围随机得到流量"/>
                            <x-system.input-limit title="时间间隔" code="traffic_limit_time" :value="$traffic_limit_time" help="间隔多久才可以再次签到"/>
                            <x-system.input-limit title="流量范围" code="min_rand_traffic" hcode="max_rand_traffic" :value="$min_rand_traffic" :hvalue="$max_rand_traffic"
                                                  :max="$max_rand_traffic" :hmin="$min_rand_traffic" unit="MB"/>
                        </x-system.tab-pane>
                        <x-system.tab-pane id="promo">
                            <x-system.switch title="推广功能" code="referral_status" :check="$referral_status" help="关闭后用户不可见，但是不影响其正常邀请返利"/>
                            <x-system.select title="返利模式" code="referral_type" :list="['关闭' => '', '首购返利' => 1, '循环返利' => 2]" help="切换模式后旧数据不变，新的返利按新的模式计算"/>
                            <x-system.input-limit title="注册送流量" code="referral_traffic" :value="$referral_traffic" unit="MB" help="根据推广链接、邀请码注册则赠送相应的流量"/>
                            <x-system.input-limit title="返利比例" code="referral_percent" :value="$referral_percent * 100" max="100" unit="%"
                                                  help="根据推广链接注册的账号每笔消费推广人可以分成的比例 "/>
                            <x-system.input-limit title="提现限制" code="referral_money" :value="$referral_money" unit="元" help="满多少元才可以申请提现"/>
                        </x-system.tab-pane>
                        <x-system.tab-pane id="notify">
                            <x-system.select title="账号过期通知" code="account_expire_notification" :list="['邮箱' => 'mail', '站内通知' => 'database']"
                                             help="通知用户账号即将到期" multiple="1"/>
                            <x-system.input-limit title="过期警告阈值" code="expire_days" :value="$expire_days" unit="元" help="【账号过期通知】开始阈值，每日通知用户"/>
                            <x-system.select title="流量耗尽通知" code="data_exhaust_notification" :list="['邮箱' => 'mail', '站内通知' => 'database']"
                                             help="通知用户流量即将耗尽" multiple="1"/>
                            <x-system.input-limit title="流量警告阈值" code="traffic_warning_percent" :value="$traffic_warning_percent" unit="%" help="【流量耗尽通知】开始阈值，每日通知用户"/>
                            <x-system.select title="节点离线提醒" code="node_offline_notification" :list="['邮箱' => 'mail', 'Bark' => 'bark', 'ServerChan' => 'serverChan', 'Telegram' => 'telegram']"
                                             help="每10分钟检测节点离线并提醒管理员" multiple="1"/>
                            <x-system.input-limit title="离线提醒次数" code="offline_check_times" :value="$offline_check_times" unit="次" help="24小时内提醒n次后不再提醒"/>
                            <x-system.select title="节点阻断提醒" code="node_blocked_notification" :list="['邮箱' => 'mail', 'ServerChan' => 'serverChan', 'Telegram' => 'telegram']"
                                             help="每小时检测节点是否被阻断并提醒管理员" multiple="1"/>
                            <x-system.input-limit title="阻断检测提醒" code="detection_check_times" :value="$detection_check_times" max="12" unit="次"
                                                  help="提醒N次后自动下线节点，为0时不限制，不超过12"/>
                            <x-system.select title="支付成功通知" code="payment_received_notification" :list="['邮箱' => 'mail', '站内通知' => 'database', 'Telegram' => 'telegram']"
                                             help="用户支付订单后通知用户订单状态" multiple="1"/>
                            <x-system.select title="工单关闭通知" code="ticket_closed_notification" :list="['邮箱' => 'mail', 'Bark' => 'bark', 'ServerChan' => 'serverChan', 'Telegram' => 'telegram']"
                                             help="工单关闭通知用户" multiple="1"/>
                            <x-system.select title="新工单通知" code="ticket_created_notification" :list="['邮箱' => 'mail', 'Bark' => 'bark', 'ServerChan' => 'serverChan', 'Telegram' => 'telegram']"
                                             help="新工单通知管理/用户，取决于谁创建了新工单" multiple="1"/>
                            <x-system.select title="工单回复通知" code="ticket_replied_notification" :list="['邮箱' => 'mail', 'Bark' => 'bark', 'ServerChan' => 'serverChan', 'Telegram' => 'telegram']"
                                             help="工单回复通知对方" multiple="1"/>
                            <x-system.input-test title="SCKEY" :value="$server_chan_key" code="server_chan_key" help='启用ServerChan，请务必填入本值（<a href=https://sc.ftqq.com
                                    target=_blank>申请SCKEY</a>）' holder="请到ServerChan申请" test="serverChan"/>
                            <x-system.input-test title="Bark设备号" :value="$bark_key" code="bark_key" holder="安装并打开Bark后取得" type="url"
                                                 help="推送消息到iOS设备，需要在iOS设备里装一个名为Bark的应用，取网址后的一长串代码，启用Bark，请务必填入本值" test="bark"/>
                            <x-system.switch title="PushBear" code="is_push_bear" :check="$is_push_bear"
                                             help='使用PushBear推送微信消息给用户（<a href="https://pushbear.ftqq.com/admin/#/signin" target="_blank">创建消息通道</a>）'/>
                            <x-system.input title="PushBear SendKey" :value="$push_bear_send_key" code="push_bear_send_key" help="启用PushBear，请务必填入本值" holder="创建消息通道后即可获取"/>
                            <x-system.input title="TelegramToken" :value="$telegram_token" code="telegram_token" help="启用telegram_bot，请务必填入本值" holder="在@BotFather创建后即可获取"/>
                            <x-system.input title="PushBear订阅二维码" :value="$push_bear_qrcode" code="push_bear_qrcode" help="创建消息通道后，在二维码上点击右键“复制图片地址”并粘贴至此处"
                                            holder="填入消息通道的二维码URL" type="url"/>
                        </x-system.tab-pane>
                        <x-system.tab-pane id="auto">
                            <x-system.switch title="自动清除日志" code="is_clear_log" :check="$is_clear_log" help='（推荐）启用后自动清除无用日志'/>
                            <x-system.switch title="流量自动重置" code="reset_traffic" :check="$reset_traffic" help='用户会按其购买套餐的日期自动重置可用流量'/>
                            <x-system.switch title="订阅异常自动封禁" code="is_subscribe_ban" :check="$is_subscribe_ban" help='启用后用户订阅链接请求超过设定阈值则自动封禁'/>
                            <x-system.input-limit title="订阅请求阈值" code="subscribe_ban_times" :value="$subscribe_ban_times" help="24小时内订阅链接请求次数限制"/>
                            <x-system.switch title="异常自动封号" code="is_traffic_ban" :check="$is_traffic_ban" help='1小时内流量超过异常阈值则自动封号（仅禁用代理）'/>
                            <x-system.select title="流量异常通知" code="data_anomaly_notification" :list="['邮箱' => 'mail', 'Bark' => 'bark', 'ServerChan' => 'serverChan']"
                                             help="1小时内流量超过异常阈值通知超管" multiple="1"/>
                            <x-system.input-limit title="流量异常阈值" code="traffic_ban_value" :value="$traffic_ban_value" min="1" unit="GB" help="1小时内超过该值，则触发自动封号"/>
                            <x-system.input-limit title="封号时长" code="traffic_ban_time" :value="$traffic_ban_time" unit="分钟" help="触发流量异常导致用户被封禁的时长，到期后自动解封"/>
                            <x-system.switch title="端口回收机制" code="auto_release_port" :check="$auto_release_port" help="被封禁/过期{{config('tasks.release_port')}}天的账号端口自动释放"/>
                            <x-system.switch title="过期自动封禁" code="is_ban_status" :check="$is_ban_status" help="(慎重)封禁整个账号会重置账号的所有数据且会导致用户无法登录,不开启状态下只封禁用户代理"/>
                            <x-system.select title="节点使用报告" code="node_daily_notification" :list="['邮箱' => 'mail', 'ServerChan' => 'serverChan']"
                                             help="报告各节点流量昨日消耗情况" multiple="1"/>
                        </x-system.tab-pane>
                        <x-system.tab-pane id="other">
                            @if($errors->any())
                                <x-alert type="danger" :message="$errors->all()"/>
                            @endif
                            @if (Session::has('successMsg'))
                                <x-alert type="success" :message="Session::get('successMsg')"/>
                            @endif
                            <form action="{{route('admin.system.extend')}}" method="post" enctype="multipart/form-data" class="upload-form col-lg-12 row" role="form"
                                  id="setExtend">@csrf
                                <x-system.input-file title="首页LOGO" code="website_home_logo" :value="$website_home_logo"/>
                                <x-system.input-file title="站内LOGO" code="website_logo" :value="$website_logo"/>
                            </form>
                            <x-system.textarea title="统计代码" code="website_analytics" :value="$website_analytics" help="统计JS"/>
                            <x-system.textarea title="客服代码" code="website_customer_service" :value="$website_customer_service" help="客服JS"/>
                        </x-system.tab-pane>
                        <div class="tab-pane" id="payment" role="tabpanel">
                            <div class="tab-content pb-100">
                                <x-system.tab-pane id="paymentSetting" :active="true">
                                    <x-system.select title="支付宝支付" code="is_AliPay"
                                                     :list="['关闭' => '', 'F2F' => 'f2fpay', '码支付' => 'codepay', '易支付' => 'epay', '海狸支付' => 'paybeaver', '平头哥支付' => 'theadpay', '麻瓜宝' => 'bitpayx']"/>
                                    <x-system.select title="QQ钱包" code="is_QQPay" :list="['关闭' => '', '码支付' => 'codepay', '易支付' => 'epay']"/>
                                    <x-system.select title="微信支付" code="is_WeChatPay"
                                                     :list="['关闭' => '', '码支付' => 'codepay', 'PayJS' => 'payjs', '易支付' => 'epay', '海狸支付' => 'paybeaver', '麻瓜宝' => 'bitpayx']"/>
                                    <x-system.select title="特殊支付" code="is_otherPay" :list="['关闭' => '', '麻瓜宝' => 'bitpayx', 'PayPal' => 'paypal', 'Stripe' => 'stripe']"/>
                                    <x-system.input title="自定义商品名称" :value="$subject_name" code="subject_name" help="用于在支付渠道的商品标题显示"/>
                                    <x-system.input title="通用支付回调地址" :value="$website_callback_url" code="website_callback_url"
                                                    help="防止因为网站域名被DNS投毒后导致支付无法正常回调，需带http://或https://" :holder="'默认为 '.$website_url" type="url"/>
                                </x-system.tab-pane>
                                <x-system.tab-pane id="AlipayF2F">
                                    <div class="form-group col-lg-6 d-flex">
                                        <label class="col-md-3 col-form-label">支付宝F2F</label>
                                        <div class="col-md-9">
                                            本功能需要<a href="https://open.alipay.com/platform/appManage.htm?#/create/" target="_blank">蚂蚁金服开放平台</a>申请权限及应用
                                        </div>
                                    </div>
                                    <x-system.input title="应用ID" :value="$f2fpay_app_id" code="f2fpay_app_id" help="即：APPID"/>
                                    <x-system.input title="应用私钥" :value="$f2fpay_private_key" code="f2fpay_private_key" help="生成秘钥软件生成时，产生的应用秘钥"/>
                                    <x-system.input title="支付宝公钥" :value="$f2fpay_public_key" code="f2fpay_public_key" help="注意不是应用公钥！"/>
                                </x-system.tab-pane>
                                <x-system.tab-pane id="CodePay">
                                    <div class="form-group col-lg-6 d-flex">
                                        <label class="col-md-3 col-form-label">码支付</label>
                                        <div class="col-md-7">
                                            请到 <a href="https://codepay.fateqq.com/i/377289" target="_blank">码支付</a>申请账号，然后下载登录其挂机软件
                                        </div>
                                    </div>
                                    <x-system.input title="请求URL" :value="$codepay_url" code="codepay_url" holder="https://codepay.fateqq.com/creat_order/?" type="url"/>
                                    <x-system.input title="码支付ID" :value="$codepay_id" code="codepay_id"/>
                                    <x-system.input title="通信密钥" :value="$codepay_key" code="codepay_key"/>
                                </x-system.tab-pane>
                                <x-system.tab-pane id="EPay">
                                    <div class="form-group col-lg-6 d-flex">
                                        <label class="col-md-3 col-form-label">易支付</label>
                                        @can('admin.test.epay')
                                            <div class="col-md-7">
                                                <button class="btn btn-primary" type="button" onclick="epayInfo()">查询</button>
                                            </div>
                                        @endcan
                                    </div>
                                    <x-system.input title="接口对接地址" :value="$epay_url" code="epay_url" holder="https://www.example.com" type="url"/>
                                    <x-system.input title="商户ID" :value="$epay_mch_id" code="epay_mch_id"/>
                                    <x-system.input title="商户密钥" :value="$epay_key" code="epay_key"/>
                                </x-system.tab-pane>
                                <x-system.tab-pane id="PayJs">
                                    <div class="form-group col-lg-6 d-flex">
                                        <label class="col-md-3 col-form-label">PayJs</label>
                                        <div class="col-md-7">
                                            请到<a href="https://payjs.cn/ref/zgxjnb" target="_blank">PayJs</a> 申请账号
                                        </div>
                                    </div>
                                    <x-system.input title="商户号" :value="$payjs_mch_id" code="payjs_mch_id"
                                                    help='在<a href="https://payjs.cn/dashboard/member" target="_blank">本界面</a>获取信息'/>
                                    <x-system.input title="通信密钥" :value="$payjs_key" code="payjs_key"/>
                                </x-system.tab-pane>
                                <x-system.tab-pane id="MugglePay">
                                    <div class="form-group col-lg-6 d-flex">
                                        <label class="col-md-3 col-form-label">麻瓜宝 MugglePay</label>
                                        <div class="col-md-7">
                                            请到<a href="https://merchants.mugglepay.com/user/register?ref=MP904BEBB79FE0" target="_blank">麻瓜宝 MugglePay</a>申请账号
                                        </div>
                                    </div>
                                    <x-system.input title="应用密钥" :value="$bitpay_secret" code="bitpay_secret"
                                                    help='在<a href="https://merchants.mugglepay.com/basic/api" target="_blank">API设置</a>中获取后台服务器的秘钥'/>
                                </x-system.tab-pane>
                                <x-system.tab-pane id="PayPal">
                                    <div class="form-group col-lg-6 d-flex">
                                        <label class="col-md-3 col-form-label">PayPal</label>
                                        <div class="col-md-7">
                                            使用商家账号登录<a href="https://www.paypal.com/businessprofile/mytools/apiaccess/firstparty" target="_blank">API凭证申请页</a>, 同意并获取设置信息
                                        </div>
                                    </div>
                                    <x-system.input title="API用户名" :value="$paypal_username" code="paypal_username"/>
                                    <x-system.input title="API密码" :value="$paypal_password" code="paypal_password"/>
                                    <x-system.input title="签名" :value="$paypal_secret" code="paypal_secret"/>
                                    {{--<x-system.input title="证书" :value="$paypal_certificate" code="paypal_certificate"/>--}}
                                    {{--<x-system.input title="应用ID" :value="$paypal_app_id" code="paypal_app_id"/>--}}
                                </x-system.tab-pane>
                                <x-system.tab-pane id="Stripe">
                                    <div class="form-group col-lg-6 d-flex">
                                        <label class="col-md-3 col-form-label">Stripe</label>
                                    </div>
                                    <x-system.input title="Public Key" :value="$stripe_public_key" code="stripe_public_key"/>
                                    <x-system.input title="Secret Key" :value="$stripe_secret_key" code="stripe_secret_key"/>
                                    <x-system.input title="WebHook Signing secret" :value="$stripe_signing_secret" code="stripe_signing_secret"/>
                                </x-system.tab-pane>
                                <x-system.tab-pane id="PayBeaver">
                                    <div class="form-group col-lg-6 d-flex">
                                        <label class="col-md-3 col-form-label">海狸支付 PayBeaver</label>
                                        <div class="col-md-7">
                                            请到<a href="https://merchant.paybeaver.com/?aff_code=iK4GNuX8" target="_blank">海狸支付 PayBeaver</a>申请账号
                                        </div>
                                    </div>
                                    <x-system.input title="App ID" :value="$paybeaver_app_id" code="paybeaver_app_id"
                                                    help='<a href="https://merchant.paybeaver.com/" target="_blank">商户中心</a> -&gt; 开发者 -&gt; App ID'/>
                                    <x-system.input title="App Secret" :value="$paybeaver_app_secret" code="paybeaver_app_secret"
                                                    help='<a href="https://merchant.paybeaver.com/" target="_blank">商户中心</a> -&gt; 开发者 -&gt; App Secret'/>
                                </x-system.tab-pane>
                                <x-system.tab-pane id="THeadPay">
                                    <div class="form-group col-lg-6 d-flex">
                                        <label class="col-md-3 col-form-label">平头哥支付 THeadPay</label>
                                        <div class="col-md-7">
                                            请到<a href="https://theadpay.com/" target="_blank">平头哥支付 THeadPay</a>申请账号
                                        </div>
                                    </div>
                                    <x-system.input title="接口地址" :value="$theadpay_url" code="theadpay_url" type="url"/>
                                    <x-system.input title="商家ID" :value="$theadpay_mchid" code="theadpay_mchid"/>
                                    <x-system.input title="商家密钥" :value="$theadpay_key" code="theadpay_key"/>
                                </x-system.tab-pane>
                            </div>
                            <ul class="nav nav-tabs nav-tabs-bottom nav-tabs-line dropup" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#paymentSetting" aria-controls="paymentSetting" role="tab">支付设置</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#AlipayF2F" aria-controls="AlipayF2F" role="tab">支付宝F2F</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#CodePay" aria-controls="CodePay" role="tab">码支付</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#EPay" aria-controls="EPay" role="tab">易支付</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#PayJs" aria-controls="PayJs" role="tab">PayJs</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#MugglePay" aria-controls="MugglePay" role="tab">MugglePay</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#PayPal" aria-controls="PayPal" role="tab">PayPal</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#Stripe" aria-controls="Stripe" role="tab">Stripe</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#PayBeaver" aria-controls="PayBeaver" role="tab">PayBeaver</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#THeadPay" aria-controls="THeadPay" role="tab">平头哥支付</a>
                                </li>
                                <li class="nav-item dropdown" style="display: none;">
                                    <a class="dropdown-toggle nav-link" data-toggle="dropdown" href="#" aria-expanded="false" aria-haspopup="true">菜单</a>
                                    <div class="dropdown-menu" role="menu">
                                        <a class="dropdown-item active" data-toggle="tab" href="#paymentSetting" aria-controls="paymentSetting" role="tab">支付设置</a>
                                        <a class="dropdown-item" data-toggle="tab" href="#AlipayF2F" aria-controls="AlipayF2F" role="tab">支付宝F2F</a>
                                        <a class="dropdown-item" data-toggle="tab" href="#CodePay" aria-controls="CodePay" role="tab">码支付</a>
                                        <a class="dropdown-item" data-toggle="tab" href="#EPay" aria-controls="EPay" role="tab">易支付</a>
                                        <a class="dropdown-item" data-toggle="tab" href="#PayJs" aria-controls="PayJs" role="tab">PayJs</a>
                                        <a class="dropdown-item" data-toggle="tab" href="#MugglePay" aria-controls="MugglePay" role="tab">MugglePay</a>
                                        <a class="dropdown-item" data-toggle="tab" href="#PayPal" aria-controls="PayPal" role="tab">PayPal</a>
                                        <a class="dropdown-item" data-toggle="tab" href="#Stripe" aria-controls="Stripe" role="tab">Stripe</a>
                                        <a class="dropdown-item" data-toggle="tab" href="#PayBeaver" aria-controls="PayBeaver" role="tab">PayBeaver</a>
                                        <a class="dropdown-item" data-toggle="tab" href="#THeadPay" aria-controls="THeadPay" role="tab">平头哥支付</a>
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
            $('#is_invite_register').selectpicker('val', '{{$is_invite_register}}');
            $('#is_activate_account').selectpicker('val', '{{$is_activate_account}}');
            $('#ddns_mode').selectpicker('val', '{{$ddns_mode}}');
            $('#is_captcha').selectpicker('val', '{{$is_captcha}}');
            $('#referral_type').selectpicker('val', '{{$referral_type}}');
            $('#is_email_filtering').selectpicker('val', '{{$is_email_filtering}}');
            $('#is_AliPay').selectpicker('val', '{{$is_AliPay}}');
            $('#is_QQPay').selectpicker('val', '{{$is_QQPay}}');
            $('#is_WeChatPay').selectpicker('val', '{{$is_WeChatPay}}');
            $('#is_otherPay').selectpicker('val', '{{$is_otherPay}}');
            $('#account_expire_notification').selectpicker('val', {!! $account_expire_notification !!});
            $('#data_anomaly_notification').selectpicker('val', {!! $data_anomaly_notification !!});
            $('#data_exhaust_notification').selectpicker('val', {!! $data_exhaust_notification !!});
            $('#node_blocked_notification').selectpicker('val', {!! $node_blocked_notification !!});
            $('#node_daily_notification').selectpicker('val', {!! $node_daily_notification !!});
            $('#node_offline_notification').selectpicker('val', {!! $node_offline_notification !!});
            $('#password_reset_notification').selectpicker('val', {!! $password_reset_notification !!});
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
                includes(op[i].value)
                    ? op[i].disabled = false
                    : op[i].disabled = true;
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
            $.post('{{route('admin.system.update')}}', {_token: '{{csrf_token()}}', name: systemItem, value: value}, function(ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, icon: 'success', timer: 1500, showConfirmButton: false});
                } else {
                    swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
                }
            });
            @else
            swal.fire({title: '您没有权限修改系统参数！', icon: 'error', timer: 1500, showConfirmButton: false});
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

        // 发送Bark测试消息
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
                        html: '商户状态: ' + ret.data['active'] + ' | 账号余额： ' + ret.data['money'] + ' | 结算账号：' + ret.data['account'] +
                            '<br\><br\>渠道手续费：【支付宝 - ' + (100 - ret.data['alirate']) + '% | 微信 - ' + (100 - ret.data['wxrate']) +
                            '% | QQ钱包 - ' + (100 - ret.data['qqrate']) + '%】<br\><br\> 请按照支付平台的介绍为准，本信息纯粹为Api获取信息',
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
