@extends('_layout')
@section('title', sysConfig('website_name'))
@section('layout_css')
    <link href="/assets/global/fonts/font-awesome/css/all.min.css" rel="stylesheet">
    @yield('css')
@endsection
@section('body_class', 'dashboard')
@section('layout_content')
    <nav class="site-navbar navbar navbar-default navbar-fixed-top navbar-mega {{config('theme.navbar.inverse')}} {{config('theme.navbar.skin')}}" role="navigation">
        <div class="navbar-header">
            <button type="button" class="navbar-toggler hamburger hamburger-close navbar-toggler-left hided" data-toggle="menubar">
                <span class="sr-only">{{trans('common.toggle_action', ['action' => trans('common.function.navigation')])}}</span>
                <span class="hamburger-bar"></span>
            </button>
            <button type="button" class="navbar-toggler collapsed" data-target="#site-navbar-collapse" data-toggle="collapse">
                <i class="icon wb-more-horizontal" aria-hidden="true"></i>
            </button>
            <div class="navbar-brand navbar-brand-center">
                <img src="{{sysConfig('website_logo')? asset(sysConfig('website_logo')) :'/assets/images/logo64.png'}}" class="navbar-brand-logo" alt="logo"/>
                <span class="navbar-brand-text hidden-xs-down"> {{sysConfig('website_name')}}</span>
            </div>
        </div>
        <div class="navbar-container container-fluid">
            <div class="collapse navbar-collapse navbar-collapse-toolbar" id="site-navbar-collapse">
                <ul class="nav navbar-toolbar">
                    <li class="nav-item hidden-float" id="toggleMenubar">
                        <a class="nav-link" data-toggle="menubar" href="#" role="button">
                            <i class="icon hamburger hamburger-arrow-left">
                                <span class="sr-only">{{trans('common.toggle_action', ['action' => trans('common.function.menubar')])}}</span>
                                <span class="hamburger-bar"></span>
                            </i>
                        </a>
                    </li>
                    <li class="nav-item hidden-sm-down">
                        <a class="nav-link icon icon-fullscreen" data-toggle="fullscreen" href="#" role="button">
                            <span class="sr-only">{{trans('common.toggle_action', ['action' => trans('common.function.fullscreen')])}}</span>
                        </a>
                    </li>
                </ul>
                <ul class="nav navbar-toolbar navbar-right navbar-toolbar-right">
                    <li class="nav-item dropdown">
                        <a class="nav-link navbar-avatar" data-toggle="dropdown" href="#" aria-expanded="false" data-animation="scale-up" role="button">
                        <span class="avatar avatar-online">
                            <img src="/assets/images/avatar.svg" alt="..."/>
                            <i></i>
                        </span>
                        </a>
                        <div class="dropdown-menu" role="menu">
                            <a class="dropdown-item" href="/" role="menuitem">
                                <i class="icon wb-settings" aria-hidden="true"></i>
                                个人中心
                            </a>
                            <div class="dropdown-divider" role="presentation"></div>
                            <a class="dropdown-item" href="{{route('logout')}}" role="menuitem">
                                <i class="icon wb-power" aria-hidden="true"></i>
                                {{trans('auth.logout')}}
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="site-menubar {{config('theme.sidebar')}}">
        <div class="site-menubar-body">
            <ul class="site-menu" data-plugin="menu">
                @can('admin.index')
                    <li class="site-menu-item {{request()->routeIs('admin.index') ? 'active open' : ''}}">
                        <a href="{{route('admin.index')}}">
                            <i class="site-menu-icon wb-dashboard" aria-hidden="true"></i>
                            <span class="site-menu-title">管理中心</span>
                        </a>
                    </li>
                @endcan
                @canany(['admin.user.index', 'admin.user.group.index', 'admin.log.credit', 'admin.subscribe.index'])
                    <li class="site-menu-item has-sub {{request()->routeIs('admin.user.*', 'admin.log.credit', 'admin.subscribe.*') ? 'active open' : ''}}">
                        <a href="javascript:void(0)">
                            <i class="site-menu-icon wb-user" aria-hidden="true"></i>
                            <span class="site-menu-title">用户系统</span>
                        </a>
                        <ul class="site-menu-sub">
                            @can('admin.user.index')
                                <li class="site-menu-item {{request()->routeIs('admin.user.index', 'admin.user.edit', 'admin.user.monitor', 'admin.user.online', 'admin.user.online', 'admin.user.export') ? 'active open' : ''}}">
                                    <a href="{{route('admin.user.index')}}">
                                        <span class="site-menu-title">用户管理</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.user.oauth')
                                <li class="site-menu-item {{request()->routeIs('admin.user.oauth') ? 'active open' : ''}}">
                                    <a href="{{route('admin.user.oauth')}}">
                                        <span class="site-menu-title">用户授权</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.user.group.index')
                                <li class="site-menu-item {{request()->routeIs('admin.user.group.*') ? 'active open' : ''}}">
                                    <a href="{{route('admin.user.group.index')}}">
                                        <span class="site-menu-title">用戶分组</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.log.credit')
                                <li class="site-menu-item {{request()->routeIs('admin.log.credit') ? 'active open' : ''}}">
                                    <a href="{{route('admin.log.credit')}}">
                                        <span class="site-menu-title">余额变动</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.subscribe.index')
                                <li class="site-menu-item {{request()->routeIs('admin.subscribe.*')? 'active open' : ''}}">
                                    <a href="{{route('admin.subscribe.index')}}">
                                        <span class="site-menu-title">订阅管理</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                @canany(['admin.permission.index', 'admin.role.index'])
                    <li class="site-menu-item has-sub {{request()->routeIs('admin.permission.*', 'admin.role.*') ? 'active open' : ''}}">
                        <a href="javascript:void(0)">
                            <i class="site-menu-icon wb-users" aria-hidden="true"></i>
                            <span class="site-menu-title">权限系统</span>
                        </a>
                        <ul class="site-menu-sub">
                            @can('admin.permission.index')
                                <li class="site-menu-item {{request()->routeIs('admin.permission.*') ? 'active open' : ''}}">
                                    <a href="{{route('admin.permission.index')}}">
                                        <span class="site-menu-title">权限列表</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.role.index')
                                <li class="site-menu-item {{request()->routeIs('admin.role.*') ? 'active open' : ''}}">
                                    <a href="{{route('admin.role.index')}}">
                                        <span class="site-menu-title">角色列表</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                @canany(['admin.ticket.index', 'admin.article.index', 'admin.marketing.push', 'admin.marketing.email'])
                    <li class="site-menu-item has-sub {{request()->routeIs('admin.ticket.*', 'admin.article.*', 'admin.marketing.*')? 'active open' : ''}}">
                        <a href="javascript:void(0)">
                            <i class="site-menu-icon wb-chat-working" aria-hidden="true"></i>
                            <span class="site-menu-title">客服系统</span>
                            @can('admin.ticket.index')
                                @php
                                    $openTicket = App\Models\Ticket::whereStatus(0)->count()
                                @endphp
                                @if($openTicket > 0)
                                    <div class="site-menu-badge">
                                        <span class="badge badge-pill badge-success">{{$openTicket}}</span>
                                    </div>
                                @endif
                            @endcan
                        </a>
                        <ul class="site-menu-sub">
                            @can('admin.ticket.index')
                                <li class="site-menu-item {{request()->routeIs('admin.ticket.*') ? 'active open' : ''}}">
                                    <a href="{{route('admin.ticket.index')}}">
                                        <span class="site-menu-title">服务工单</span>
                                        @if($openTicket > 0)
                                            <div class="site-menu-label">
                                                <span class="badge badge-danger badge-round mr-25">{{$openTicket}}</span>
                                            </div>
                                        @endif
                                    </a>
                                </li>
                            @endcan
                            @can('admin.article.index')
                                <li class="site-menu-item {{request()->routeIs('admin.article.*')? 'active open' : ''}}">
                                    <a href="{{route('admin.article.index')}}">
                                        <span class="site-menu-title">文章管理</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.marketing.push')
                                <li class="site-menu-item {{request()->routeIs('admin.marketing.push') ? 'active open' : ''}}">
                                    <a href="{{route('admin.marketing.push')}}">
                                        <span class="site-menu-title">消息推送</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.marketing.email')
                                <li class="site-menu-item {{request()->routeIs('admin.marketing.email') ? 'active open' : ''}}">
                                    <a href="{{route('admin.marketing.email')}}">
                                        <span class="site-menu-title">邮件群发</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                @canany(['admin.node.index', 'admin.node.auth.index', 'admin.node.cert.index'])
                    <li class="site-menu-item has-sub {{request()->routeIs('admin.node.*') ? 'active open' : ''}}">
                        <a href="javascript:void(0)">
                            <i class="site-menu-icon wb-cloud" aria-hidden="true"></i>
                            <span class="site-menu-title">线路系统</span>
                        </a>
                        <ul class="site-menu-sub">
                            @can('admin.node.index')
                                <li class="site-menu-item {{request()->routeIs('admin.node.index', 'admin.node.create', 'admin.node.edit')? 'active open' : ''}}">
                                    <a href="{{route('admin.node.index')}}">
                                        <span class="site-menu-title">线路管理</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.node.auth.index')
                                <li class="site-menu-item {{request()->routeIs('admin.node.auth.*') ? 'active open' : ''}}">
                                    <a href="{{route('admin.node.auth.index')}}">
                                        <span class="site-menu-title">线路授权</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.node.cert.index')
                                <li class="site-menu-item {{request()->routeIs('admin.node.cert.*') ? 'active open' : ''}}">
                                    <a href="{{route('admin.node.cert.index')}}">
                                        <span class="site-menu-title">证书列表</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                @canany(['admin.rule.index', 'admin.rule.group.index', 'admin.rule.log'])
                    <li class="site-menu-item has-sub {{request()->routeIs('admin.rule.*') ? 'active open' : ''}}">
                        <a href="javascript:void(0)">
                            <i class="site-menu-icon wb-eye" aria-hidden="true"></i>
                            <span class="site-menu-title">审计规则</span>
                        </a>
                        <ul class="site-menu-sub">
                            @can('admin.rule.index')
                                <li class="site-menu-item {{request()->routeIs('admin.rule.index') ? 'active open' : ''}}">
                                    <a href="{{route('admin.rule.index')}}">
                                        <span class="site-menu-title">规则列表</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.rule.group.index')
                                <li class="site-menu-item {{request()->routeIs('admin.rule.group.*') ? 'active open' : ''}}">
                                    <a href="{{route('admin.rule.group.index')}}">
                                        <span class="site-menu-title">规则分组</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.rule.log')
                                <li class="site-menu-item {{request()->routeIs('admin.rule.log') ? 'active open' : ''}}">
                                    <a href="{{route('admin.rule.log')}}">
                                        <span class="site-menu-title">触发记录</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                @canany(['admin.goods.index', 'admin.coupon.index', 'admin.order'])
                    <li class="site-menu-item has-sub {{request()->routeIs('admin.goods.*', 'admin.coupon.*', 'admin.order') ? 'active open' : ''}}">
                        <a href="javascript:void(0)">
                            <i class="site-menu-icon wb-shopping-cart" aria-hidden="true"></i>
                            <span class="site-menu-title">商品系统</span>
                        </a>
                        <ul class="site-menu-sub">
                            @can('admin.goods.index')
                                <li class="site-menu-item {{request()->routeIs('admin.goods.*') ? 'active open' : ''}}">
                                    <a href="{{route('admin.goods.index')}}">
                                        <span class="site-menu-title">商品管理</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.coupon.index')
                                <li class="site-menu-item {{request()->routeIs('admin.coupon.*') ? 'active open' : ''}}">
                                    <a href="{{route('admin.coupon.index')}}">
                                        <span class="site-menu-title">卡券管理</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.order')
                                <li class="site-menu-item {{request()->routeIs('admin.order') ? 'active open' : ''}}">
                                    <a href="{{route('admin.order')}}">
                                        <span class="site-menu-title">商品订单</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                @canany(['admin.invite.index', 'admin.aff.index', 'admin.aff.rebate'])
                    <li class="site-menu-item has-sub {{request()->routeIs('admin.invite.*', 'admin.aff.*') ? 'active open' : ''}}">
                        <a href="javascript:void(0)">
                            <i class="site-menu-icon wb-thumb-up" aria-hidden="true"></i>
                            <span class="site-menu-title">推广系统</span>
                            @can('admin.aff.index')
                                @php
                                    $openApply = App\Models\ReferralApply::whereStatus(0)->count()
                                @endphp
                                @if($openApply > 0)
                                    <div class="site-menu-badge">
                                        <span class="badge badge-pill badge-success">{{$openApply}}</span>
                                    </div>
                                @endif
                            @endcan
                        </a>
                        <ul class="site-menu-sub">
                            @can('admin.invite.index')
                                <li class="site-menu-item {{request()->routeIs('admin.invite.index') ? 'active open' : ''}}">
                                    <a href="{{route('admin.invite.index')}}">
                                        <span class="site-menu-title">邀请管理</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.aff.index')
                                <li class="site-menu-item {{request()->routeIs('admin.aff.index', 'admin.aff.detail') ? 'active open' : ''}}">
                                    <a href="{{route('admin.aff.index')}}">
                                        <span class="site-menu-title">提现管理</span>
                                        @if($openApply > 0)
                                            <div class="site-menu-label">
                                                <span class="badge badge-danger badge-round mr-25">{{$openApply}}</span>
                                            </div>
                                        @endif
                                    </a>
                                </li>
                            @endcan
                            @can('admin.aff.rebate')
                                <li class="site-menu-item {{request()->routeIs('admin.aff.rebate') ? 'active open' : ''}}">
                                    <a href="{{route('admin.aff.rebate')}}">
                                        <span class="site-menu-title">返利流水</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                @canany(['admin.report.accounting', 'admin.report.userAnalysis'])
                    <li class="site-menu-item has-sub {{request()->routeIs('admin.report.*') ? 'active open' : ''}}">
                        <a href="javascript:void(0)">
                            <i class="site-menu-icon wb-stats-bars" aria-hidden="true"></i>
                            <span class="site-menu-title">分析报告</span>
                        </a>
                        <ul class="site-menu-sub">
                            @can('admin.report.accounting')
                                <li class="site-menu-item {{request()->routeIs('admin.report.accounting') ? 'active open' : ''}}">
                                    <a href="{{route('admin.report.accounting')}}">
                                        <span class="site-menu-title">流水账簿</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.report.userAnalysis')
                                <li class="site-menu-item {{request()->routeIs('admin.report.userAnalysis') ? 'active open' : ''}}">
                                    <a href="{{route('admin.report.userAnalysis')}}">
                                        <span class="site-menu-title">用户分析</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                @canany(['admin.log.traffic', 'admin.log.flow', 'admin.log.ban', 'admin.log.ip', 'admin.log.online', 'admin.log.notify', 'admin.payment.callback'])
                    <li class="site-menu-item has-sub {{request()->routeIs('admin.log.traffic', 'admin.log.flow', 'admin.log.ban', 'admin.log.ip', 'admin.log.online', 'admin.log.notify', 'admin.payment.callback') ? 'active open' : ''}}">
                        <a href="javascript:void(0)">
                            <i class="site-menu-icon wb-calendar" aria-hidden="true"></i>
                            <span class="site-menu-title">日志系统</span>
                        </a>
                        <ul class="site-menu-sub">
                            @can('admin.log.traffic')
                                <li class="site-menu-item {{request()->routeIs('admin.log.traffic') ? 'active open' : ''}}">
                                    <a href="{{route('admin.log.traffic')}}">
                                        <span class="site-menu-title">流量使用</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.log.flow')
                                <li class="site-menu-item {{request()->routeIs('admin.log.flow') ? 'active open' : ''}}">
                                    <a href="{{route('admin.log.flow')}}">
                                        <span class="site-menu-title">流量变动</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.log.ban')
                                <li class="site-menu-item {{request()->routeIs('admin.log.ban') ? 'active open' : ''}}">
                                    <a href="{{route('admin.log.ban')}}">
                                        <span class="site-menu-title">封禁记录</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.log.ip')
                                <li class="site-menu-item {{request()->routeIs('admin.log.ip') ? 'active open' : ''}}">
                                    <a href="{{route('admin.log.ip')}}">
                                        <span class="site-menu-title">在线记录</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.log.online')
                                <li class="site-menu-item {{request()->routeIs('admin.log.online') ? 'active open' : ''}}">
                                    <a href="{{route('admin.log.online')}}">
                                        <span class="site-menu-title">在线监控</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.log.notify')
                                <li class="site-menu-item {{request()->routeIs('admin.log.notify') ? 'active open' : ''}}">
                                    <a href="{{route('admin.log.notify')}}">
                                        <span class="site-menu-title">通知记录</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.payment.callback')
                                <li class="site-menu-item {{request()->routeIs('admin.payment.callback') ? 'active open' : ''}}">
                                    <a href="{{route('admin.payment.callback')}}">
                                        <span class="site-menu-title">支付回调</span>
                                    </a>
                                </li>
                            @endcan
                            @can('log-viewer')
                                <li class="site-menu-item">
                                    <a href="{{route('log-viewer::dashboard')}}" target="_blank">
                                        <span class="site-menu-title">系统日志</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                @canany(['admin.tools.decompile', 'admin.tools.convert', 'admin.tools.import', 'admin.tools.analysis'])
                    <li class="site-menu-item has-sub {{request()->routeIs('admin.tools.*') ? 'active open' : ''}}">
                        <a href="javascript:void(0)">
                            <i class="site-menu-icon wb-briefcase" aria-hidden="true"></i>
                            <span class="site-menu-title">工具箱</span>
                        </a>
                        <ul class="site-menu-sub">
                            @can('admin.tools.decompile')
                                <li class="site-menu-item {{request()->routeIs('admin.tools.decompile') ? 'active open' : ''}}">
                                    <a href="{{route('admin.tools.decompile')}}">
                                        <span class="site-menu-title">反解析</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.tools.convert')
                                <li class="site-menu-item {{request()->routeIs('admin.tools.convert') ? 'active open' : ''}}">
                                    <a href="{{route('admin.tools.convert')}}">
                                        <span class="site-menu-title">格式转换</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.tools.import')
                                <li class="site-menu-item {{request()->routeIs('admin.tools.import') ? 'active open' : ''}}">
                                    <a href="{{route('admin.tools.import')}}">
                                        <span class="site-menu-title">数据导入</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.tools.analysis')
                                <li class="site-menu-item {{request()->routeIs('admin.tools.analysis') ? 'active open' : ''}}">
                                    <a href="{{route('admin.tools.analysis')}}">
                                        <span class="site-menu-title">日志分析</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                @canany(['admin.config.filter.index', 'admin.config.index', 'admin.system.index'])
                    <li class="site-menu-item has-sub {{request()->routeIs('admin.config.*', 'admin.system.index') ? 'active open' : ''}}">
                        <a href="javascript:void(0)">
                            <i class="site-menu-icon wb-settings" aria-hidden="true"></i>
                            <span class="site-menu-title">设置</span>
                        </a>
                        <ul class="site-menu-sub">
                            @can('admin.config.filter.index')
                                <li class="site-menu-item {{request()->routeIs('admin.config.filter.index') ? 'active open' : ''}}">
                                    <a href="{{route('admin.config.filter.index')}}">
                                        <span class="site-menu-title">邮箱后缀管理</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.config.index')
                                <li class="site-menu-item {{request()->routeIs('admin.config.common.*') ? 'active open' : ''}}">
                                    <a href="{{route('admin.config.index')}}">
                                        <span class="site-menu-title">通用配置</span>
                                    </a>
                                </li>
                            @endcan
                            @can('admin.system.index')
                                <li class="site-menu-item {{request()->routeIs('admin.system.index') ? 'active open' : ''}}">
                                    <a href="{{route('admin.system.index')}}">
                                        <span class="site-menu-title">系统设置</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
            </ul>
        </div>
    </div>
    <div class="page">
        <!--[if lt IE 8]>
        <p class="browserupgrade">您正在使用 <strong>过时/老旧</strong> 的浏览器。 为了您的使用体验，请
            <a href="http://browsehappy.com/">升级您的浏览器</a> <br/>You are using an <strong>outdated</strong> browser. Please
            <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.
        </p>
        <![endif]-->
        @yield('content')
    </div>
    @endsection
@section('layout_javascript')
    <!--[if lt IE 11]>
    <script src="/assets/custom/sweetalert2/polyfill.min.js"></script>
    <![endif]-->
    <script src="/assets/custom/sweetalert2/sweetalert2.all.min.js"></script>
    @yield('javascript')
@endsection
