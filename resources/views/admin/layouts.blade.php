<!DOCTYPE html>
<!--[if IE 8]>
<html lang="{{app()->getLocale()}}" class="ie8 no-js css-menubar"> <![endif]-->
<!--[if IE 9]>
<html lang="{{app()->getLocale()}}" class="ie9 no-js css-menubar"> <![endif]-->
<!--[if !IE]><!-->
<html lang="{{app()->getLocale()}}">
<!--<![endif]-->
<head>
    <meta charset="utf-8">
    <title>{{sysConfig('website_name')}}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="兔姬菌">
    <meta name="copyright" content="2017-2020©兔姬菌">
    <link href="{{asset('favicon.ico')}}" rel="shortcut icon apple-touch-icon">
    <!-- 样式表/Stylesheets -->
    <link href="/assets/global/css/bootstrap.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/css/bootstrap-extend.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/css/site.min.css" type="text/css" rel="stylesheet">
    <!-- 插件/Plugins -->
    <link href="/assets/global/vendor/animsition/animsition.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/vendor/asscrollable/asScrollable.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/vendor/slidepanel/slidePanel.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/vendor/flag-icon-css/flag-icon.min.css" type="text/css" rel="stylesheet">
    <!-- 字体/Fonts -->
    <link href="/assets/global/fonts/web-icons/web-icons.min.css" type="text/css" rel="stylesheet">
    <link href="//fonts.loli.net/css?family=Roboto:300,400,500,300italic" type="text/css" rel="stylesheet">
    <!--[if lt IE 9]>
    <script src="/assets/global/vendor/html5shiv/html5shiv.min.js" type="text/javascript"></script>
    <![endif]-->
    <!--[if lt IE 10]>
    <script src="/assets/global/vendor/media-match/media.match.min.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/respond/respond.min.js" type="text/javascript"></script>
    <![endif]-->
    <!-- Scripts -->
    <script src="/assets/global/vendor/breakpoints/breakpoints.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        Breakpoints();
    </script>
    @yield('css')
</head>

<body class="animsition dashboard">
<nav class="site-navbar navbar navbar-default navbar-fixed-top navbar-mega navbar-inverse bg-indigo-600" role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggler hamburger hamburger-close navbar-toggler-left hided" data-toggle="menubar">
            <span class="sr-only">Toggle navigation</span>
            <span class="hamburger-bar"></span>
        </button>
        <button type="button" class="navbar-toggler collapsed" data-target="#site-navbar-collapse" data-toggle="collapse">
            <i class="icon wb-more-horizontal" aria-hidden="true"></i>
        </button>
        <div class="navbar-brand navbar-brand-center">
            <img src="{{sysConfig('website_logo')? :'/assets/images/logo64.png'}}" class="navbar-brand-logo" alt="logo"/>
            <span class="navbar-brand-text hidden-xs-down"> {{sysConfig('website_name')}}</span>
        </div>
    </div>
    <div class="navbar-container container-fluid">
        <div class="collapse navbar-collapse navbar-collapse-toolbar" id="site-navbar-collapse">
            <ul class="nav navbar-toolbar">
                <li class="nav-item hidden-float" id="toggleMenubar">
                    <a class="nav-link" data-toggle="menubar" href="#" role="button">
                        <i class="icon hamburger hamburger-arrow-left">
                            <span class="sr-only">菜单</span>
                            <span class="hamburger-bar"></span>
                        </i>
                    </a>
                </li>
                <li class="nav-item hidden-sm-down">
                    <a class="nav-link icon icon-fullscreen" data-toggle="fullscreen" href="#" role="button">
                        <span class="sr-only">全屏</span>
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
                        <a class="dropdown-item" href="{{route('admin.profile')}}" role="menuitem">
                            <i class="icon wb-user" aria-hidden="true"></i>
                            {{trans('home.profile')}}
                        </a>
                        <div class="dropdown-divider" role="presentation"></div>
                        <a class="dropdown-item" href="{{route('logout')}}" role="menuitem">
                            <i class="icon wb-power" aria-hidden="true"></i>
                            {{trans('home.logout')}}
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="site-menubar site-menubar-light">
    <div class="site-menubar-body">
        <ul class="site-menu" data-plugin="menu">
            <li class="site-menu-item {{request()->routeIs('admin.index') ? 'active open' : ''}}">
                <a href="{{route('admin.index')}}">
                    <i class="site-menu-icon wb-dashboard" aria-hidden="true"></i>
                    <span class="site-menu-title">管理中心</span>
                </a>
            </li>
            <li class="site-menu-item has-sub {{request()->routeIs('admin.user.*', 'admin.log.credit', 'admin.subscribe.*') ? 'active open' : ''}}">
                <a href="javascript:void(0)">
                    <i class="site-menu-icon wb-user" aria-hidden="true"></i>
                    <span class="site-menu-title">用户系统</span>
                </a>
                <ul class="site-menu-sub">
                    <li class="site-menu-item {{request()->routeIs('admin.user.index', 'admin.user.edit', 'admin.user.monitor', 'admin.user.online', 'admin.user.online', 'admin.user.export') ? 'active open' : ''}}">
                        <a href="{{route('admin.user.index')}}">
                            <span class="site-menu-title">用户管理</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.user.group.*') ? 'active open' : ''}}">
                        <a href="{{route('admin.user.group.index')}}">
                            <span class="site-menu-title">用戶分组</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.log.credit') ? 'active open' : ''}}">
                        <a href="{{route('admin.log.credit')}}">
                            <span class="site-menu-title">余额变动</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.subscribe.*')? 'active open' : ''}}">
                        <a href="{{route('admin.subscribe.index')}}">
                            <span class="site-menu-title">订阅管理</span>
                        </a>
                    </li>
                </ul>
            </li>
            @php
                $openTicket = App\Models\Ticket::whereStatus(0)->count()
            @endphp
            <li class="site-menu-item has-sub {{request()->routeIs('admin.ticket.*', 'admin.article.*', 'admin.marketing.*')? 'active open' : ''}}">
                <a href="javascript:void(0)">
                    <i class="site-menu-icon wb-chat-working" aria-hidden="true"></i>
                    <span class="site-menu-title">客服系统</span>
                    @if($openTicket > 0)
                        <div class="site-menu-badge">
                            <span class="badge badge-pill badge-success">{{$openTicket}}</span>
                        </div>
                    @endif
                </a>
                <ul class="site-menu-sub">
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
                    <li class="site-menu-item {{request()->routeIs('admin.article.*')? 'active open' : ''}}">
                        <a href="{{route('admin.article.index')}}">
                            <span class="site-menu-title">文章管理</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.marketing.push') ? 'active open' : ''}}">
                        <a href="{{route('admin.marketing.push')}}">
                            <span class="site-menu-title">消息推送</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.marketing.email') ? 'active open' : ''}}">
                        <a href="{{route('admin.marketing.email')}}">
                            <span class="site-menu-title">邮件群发</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="site-menu-item has-sub {{request()->routeIs('admin.node.*') ? 'active open' : ''}}">
                <a href="javascript:void(0)">
                    <i class="site-menu-icon wb-grid-4" aria-hidden="true"></i>
                    <span class="site-menu-title">线路系统</span>
                </a>
                <ul class="site-menu-sub">
                    <li class="site-menu-item {{request()->routeIs('admin.node.index', 'admin.node.create', 'admin.node.edit')? 'active open' : ''}}">
                        <a href="{{route('admin.node.index')}}">
                            <span class="site-menu-title">线路管理</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.node.auth.*') ? 'active open' : ''}}">
                        <a href="{{route('admin.node.auth.index')}}">
                            <span class="site-menu-title">线路授权</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.node.cert.*') ? 'active open' : ''}}">
                        <a href="{{route('admin.node.cert.index')}}">
                            <span class="site-menu-title">证书列表</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.node.pingLog') ? 'active open' : ''}}">
                        <a href="{{route('admin.node.pingLog')}}">
                            <span class="site-menu-title">测速日志</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="site-menu-item has-sub {{request()->routeIs('admin.rule.*') ? 'active open' : ''}}">
                <a href="javascript:void(0)">
                    <i class="site-menu-icon wb-eye" aria-hidden="true"></i>
                    <span class="site-menu-title">审计规则</span>
                </a>
                <ul class="site-menu-sub">
                    <li class="site-menu-item {{request()->routeIs('admin.rule.index') ? 'active open' : ''}}">
                        <a href="{{route('admin.rule.index')}}">
                            <span class="site-menu-title">规则列表</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.rule.group.*') ? 'active open' : ''}}">
                        <a href="{{route('admin.rule.group.index')}}">
                            <span class="site-menu-title">规则分组</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.rule.log') ? 'active open' : ''}}">
                        <a href="{{route('admin.rule.log')}}">
                            <span class="site-menu-title">触发记录</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="site-menu-item has-sub {{request()->routeIs('admin.goods.*', 'admin.coupon.*', 'admin.order') ? 'active open' : ''}}">
                <a href="javascript:void(0)">
                    <i class="site-menu-icon wb-shopping-cart" aria-hidden="true"></i>
                    <span class="site-menu-title">商品系统</span>
                </a>
                <ul class="site-menu-sub">
                    <li class="site-menu-item {{request()->routeIs('admin.goods.*') ? 'active open' : ''}}">
                        <a href="{{route('admin.goods.index')}}">
                            <span class="site-menu-title">商品管理</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.coupon.*') ? 'active open' : ''}}">
                        <a href="{{route('admin.coupon.index')}}">
                            <span class="site-menu-title">卡券管理</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.order') ? 'active open' : ''}}">
                        <a href="{{route('admin.order')}}">
                            <span class="site-menu-title">商品订单</span>
                        </a>
                    </li>
                </ul>
            </li>
            @php
                $openApply = App\Models\ReferralApply::whereStatus(0)->count()
            @endphp
            <li class="site-menu-item has-sub {{request()->routeIs('admin.invite', 'admin.aff.*') ? 'active open' : ''}}">
                <a href="javascript:void(0)">
                    <i class="site-menu-icon wb-thumb-up" aria-hidden="true"></i>
                    <span class="site-menu-title">推广系统</span>
                    @if($openApply > 0)
                        <div class="site-menu-badge">
                            <span class="badge badge-pill badge-success">{{$openApply}}</span>
                        </div>
                    @endif
                </a>
                <ul class="site-menu-sub">
                    <li class="site-menu-item {{request()->routeIs('admin.invite') ? 'active open' : ''}}">
                        <a href="{{route('admin.invite')}}">
                            <span class="site-menu-title">邀请管理</span>
                        </a>
                    </li>
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
                    <li class="site-menu-item {{request()->routeIs('admin.aff.rebate') ? 'active open' : ''}}">
                        <a href="{{route('admin.aff.rebate')}}">
                            <span class="site-menu-title">返利流水</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="site-menu-item has-sub {{request()->routeIs('admin.log.traffic', 'admin.log.flow', 'admin.log.ban', 'admin.log.ip', 'admin.log.online', 'admin.log.notify', 'admin.payment.callback') ? 'active open' : ''}}">
                <a href="javascript:void(0)">
                    <i class="site-menu-icon wb-calendar" aria-hidden="true"></i>
                    <span class="site-menu-title">日志系统</span>
                </a>
                <ul class="site-menu-sub">
                    <li class="site-menu-item {{request()->routeIs('admin.log.traffic') ? 'active open' : ''}}">
                        <a href="{{route('admin.log.traffic')}}">
                            <span class="site-menu-title">流量使用</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.log.flow') ? 'active open' : ''}}">
                        <a href="{{route('admin.log.flow')}}">
                            <span class="site-menu-title">流量变动</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.log.ban') ? 'active open' : ''}}">
                        <a href="{{route('admin.log.ban')}}">
                            <span class="site-menu-title">封禁记录</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.log.ip') ? 'active open' : ''}}">
                        <a href="{{route('admin.log.ip')}}">
                            <span class="site-menu-title">在线记录</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.log.online') ? 'active open' : ''}}">
                        <a href="{{route('admin.log.online')}}">
                            <span class="site-menu-title">在线监控</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.log.notify') ? 'active open' : ''}}">
                        <a href="{{route('admin.log.notify')}}">
                            <span class="site-menu-title">通知记录</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.payment.callback') ? 'active open' : ''}}">
                        <a href="{{route('admin.payment.callback')}}">
                            <span class="site-menu-title">支付回调</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.log.viewer') ? 'active open' : ''}}">
                        <a href="{{route('admin.log.viewer')}}" target="_blank">
                            <span class="site-menu-title">系统运行</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="site-menu-item has-sub {{request()->routeIs('admin.tools.*') ? 'active open' : ''}}">
                <a href="javascript:void(0)">
                    <i class="site-menu-icon wb-briefcase" aria-hidden="true"></i>
                    <span class="site-menu-title">工具箱</span>
                </a>
                <ul class="site-menu-sub">
                    <li class="site-menu-item {{request()->routeIs('admin.tools.decompile') ? 'active open' : ''}}">
                        <a href="{{route('admin.tools.decompile')}}">
                            <span class="site-menu-title">反解析</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.tools.convert') ? 'active open' : ''}}">
                        <a href="{{route('admin.tools.convert')}}">
                            <span class="site-menu-title">格式转换</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.tools.import') ? 'active open' : ''}}">
                        <a href="{{route('admin.tools.import')}}">
                            <span class="site-menu-title">数据导入</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.tools.analysis') ? 'active open' : ''}}">
                        <a href="{{route('admin.tools.analysis')}}">
                            <span class="site-menu-title">日志分析</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="site-menu-item has-sub {{request()->routeIs('admin.config.*', 'admin.system') ? 'active open' : ''}}">
                <a href="javascript:void(0)">
                    <i class="site-menu-icon wb-settings" aria-hidden="true"></i>
                    <span class="site-menu-title">设置</span>
                </a>
                <ul class="site-menu-sub">
                    <li class="site-menu-item {{request()->routeIs('admin.config.filter.index') ? 'active open' : ''}}">
                        <a href="{{route('admin.config.filter.index')}}">
                            <span class="site-menu-title">邮箱后缀管理</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.config.common.*') ? 'active open' : ''}}">
                        <a href="{{route('admin.config')}}">
                            <span class="site-menu-title">通用配置</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{request()->routeIs('admin.system') ? 'active open' : ''}}">
                        <a href="{{route('admin.system')}}">
                            <span class="site-menu-title">系统设置</span>
                        </a>
                    </li>
                </ul>
            </li>
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
<!-- 核心/Core -->
<script src="/assets/global/vendor/babel-external-helpers/babel-external-helpers.js" type="text/javascript"></script>
<script src="/assets/global/vendor/jquery/jquery.min.js" type="text/javascript"></script>
<script src="/assets/global/vendor/popper-js/umd/popper.min.js" type="text/javascript"></script>
<script src="/assets/global/vendor/bootstrap/bootstrap.min.js" type="text/javascript"></script>
<script src="/assets/global/vendor/animsition/animsition.min.js" type="text/javascript"></script>
<script src="/assets/global/vendor/mousewheel/jquery.mousewheel.js" type="text/javascript"></script>
<script src="/assets/global/vendor/asscrollbar/jquery-asScrollbar.min.js" type="text/javascript"></script>
<script src="/assets/global/vendor/asscrollable/jquery-asScrollable.min.js" type="text/javascript"></script>
<script src="/assets/global/vendor/ashoverscroll/jquery-asHoverScroll.min.js" type="text/javascript"></script>
<!-- 插件/Plugins -->
<script src="/assets/global/vendor/screenfull/screenfull.js" type="text/javascript"></script>
<script src="/assets/global/vendor/slidepanel/jquery-slidePanel.min.js" type="text/javascript"></script>
<!--[if lt IE 11]>
<script src="/assets/custom/Plugin/sweetalert2/polyfill.min.js" type="text/javascript"></script>
<![endif]-->
<script src="/assets/custom/Plugin/sweetalert2/sweetalert2.all.min.js" type="text/javascript"></script>
<!-- 脚本/Scripts -->
<script src="/assets/global/js/Component.js" type="text/javascript"></script>
<script src="/assets/global/js/Plugin.js" type="text/javascript"></script>
<script src="/assets/global/js/Base.js" type="text/javascript"></script>
<script src="/assets/global/js/Config.js" type="text/javascript"></script>
<script src="/assets/js/Section/Menubar.js" type="text/javascript"></script>
<script src="/assets/js/Section/Sidebar.js" type="text/javascript"></script>
<script src="/assets/js/Section/PageAside.js" type="text/javascript"></script>
<script src="/assets/js/Plugin/menu.js" type="text/javascript"></script>
<!-- 设置/Config -->
<script src="/assets/global/js/config/colors.js" type="text/javascript"></script>
<script type="text/javascript">
  Config.set('assets', '/assets');
</script>
<!-- 页面/Page -->
<script src="/assets/js/Site.js" type="text/javascript"></script>
<script src="/assets/global/js/Plugin/asscrollable.js" type="text/javascript"></script>
<script src="/assets/global/js/Plugin/slidepanel.js" type="text/javascript"></script>
<script src="/assets/custom/Plugin/js-cookie/js.cookie.min.js" type="text/javascript"></script>
<script type="text/javascript">
  (function(document, window, $) {
    'use strict';
    const Site = window.Site;
    $(document).ready(function() {
      Site.run();
    });
  })(document, window, jQuery);
</script>
@yield('javascript')
</body>
</html>
