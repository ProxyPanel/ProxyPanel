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
    <title>{{\App\Components\Helpers::systemConfig()['website_name']}}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="兔姬菌">
    <meta name="copyright" content="2017-2019©兔姬菌">
    <link rel="shortcut icon" href="{{asset('favicon.ico')}}">
    <!-- 样式表/Stylesheets -->
    <link rel="stylesheet" href="/assets/global/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/global/css/bootstrap-extend.min.css">
    <link rel="stylesheet" href="/assets/css/site.min.css">

    <!-- 插件/Plugins -->
    <link rel="stylesheet" href="/assets/global/vendor/animsition/animsition.min.css">
    <link rel="stylesheet" href="/assets/global/vendor/asscrollable/asScrollable.min.css">
    <link rel="stylesheet" href="/assets/global/vendor/intro-js/introjs.min.css">
    <link rel="stylesheet" href="/assets/global/vendor/slidepanel/slidePanel.min.css">
    <link rel="stylesheet" href="/assets/global/vendor/flag-icon-css/flag-icon.min.css">
    @yield('css')
    <link rel="stylesheet" href="/assets/custom/Plugin/sweetalert2/sweetalert2.min.css">

    <!-- 字体/Fonts -->
    <link rel="stylesheet" href="/assets/global/fonts/web-icons/web-icons.min.css">
    <link rel="stylesheet" href="/assets/global/fonts/brand-icons/brand-icons.min.css">
    <link rel='stylesheet' href='https://fonts.loli.net/css?family=Roboto:300,400,500,300italic'>
    <!--[if lt IE 9]>
    <script src="/assets/global/vendor/html5shiv/html5shiv.min.js" type="text/javascript"></script>
    <![endif]-->
    <!--[if lt IE 10]>
    <script src="/assets/global/vendor/media-match/media.match.min.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/respond/respond.min.js" type="text/javascript"></script>
    <![endif]-->
    <!-- Scripts -->
    <script src="/assets/global/vendor/breakpoints/breakpoints.min.js" type="text/javascript"></script>
    <script>
        Breakpoints();
    </script>
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
            @if(\App\Components\Helpers::systemConfig()['website_logo'])
                <img class="navbar-brand-logo" src="{{\App\Components\Helpers::systemConfig()['website_logo']}}">
            @else
                <img class="navbar-brand-logo" src="/assets/images/logo64.png" alt="Otaku Logo">
            @endif
            <span class="navbar-brand-text hidden-xs-down"> {{\App\Components\Helpers::systemConfig()['website_name']}}</span>
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
                <li class="nav-item dropdown" id="toggerUsermenu">
                    <a class="nav-link navbar-avatar" data-toggle="dropdown" href="#" aria-expanded="false" data-animation="scale-up" role="button">
                <span class="avatar avatar-online">
                  <img src="/assets/images/avatar.png" alt="...">
                </span>
                    </a>
                    <div class="dropdown-menu" role="menu">
                        <a class="dropdown-item" href="/" role="menuitem">
                            <i class="icon wb-settings" aria-hidden="true"></i>
                            个人中心
                        </a>
                        <a class="dropdown-item" href="/admin/profile" role="menuitem">
                            <i class="icon wb-user" aria-hidden="true"></i>
                            {{trans('home.profile')}}
                        </a>
                        <div class="dropdown-divider" role="presentation"></div>
                        <a class="dropdown-item" href="/logout" role="menuitem">
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
            <li class="site-menu-item {{in_array(Request::path(), ['admin']) ? 'active open' : ''}}">
                <a href="/admin">
                    <i class="site-menu-icon wb-dashboard" aria-hidden="true"></i>
                    <span class="site-menu-title">管理中心</span>
                </a>
            </li>
            <li class="site-menu-item has-sub {{in_array(Request::path(), ['admin/userList', 'admin/addUser', 'admin/editUser', 'admin/export', 'admin/userMonitor', 'subscribe/subscribeList', 'subscribe/deviceList', 'admin/userBanLogList', 'admin/userOnlineIPList', 'admin/onlineIPMonitor', 'admin/userBalanceLogList', 'admin/userTrafficLogList']) ? 'active open' : ''}}">
                <a href="javascript:void(0)">
                    <i class="site-menu-icon wb-user" aria-hidden="true"></i>
                    <span class="site-menu-title">用户系统</span>
                </a>
                <ul class="site-menu-sub">
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/userList', 'admin/addUser', 'admin/editUser', 'admin/export', 'admin/userMonitor']) ? 'active open' : ''}}">
                        <a href="/admin/userList" class="animsition-link">
                            <span class="site-menu-title">用户管理</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['subscribe/subscribeList']) ? 'active open' : ''}}">
                        <a href="/subscribe/subscribeList" class="animsition-link">
                            <span class="site-menu-title">订阅管理</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['subscribe/deviceList']) ? 'active open' : ''}}">
                        <a href="/subscribe/deviceList" class="animsition-link">
                            <span class="site-menu-title">订阅设备</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/userBanLogList']) ? 'active open' : ''}}">
                        <a href="/admin/userBanLogList" class="animsition-link">
                            <span class="site-menu-title">封禁记录</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/userOnlineIPList']) ? 'active open' : ''}}">
                        <a href="/admin/userOnlineIPList" class="animsition-link">
                            <span class="site-menu-title">在线记录</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/onlineIPMonitor']) ? 'active open' : ''}}">
                        <a href="/admin/onlineIPMonitor" class="animsition-link">
                            <span class="site-menu-title">在线监控</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/userBalanceLogList']) ? 'active open' : ''}}">
                        <a href="/admin/userBalanceLogList" class="animsition-link">
                            <span class="site-menu-title">余额变动</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/userTrafficLogList']) ? 'active open' : ''}}">
                        <a href="/admin/userTrafficLogList" class="animsition-link">
                            <span class="site-menu-title">流量变动</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="site-menu-item has-sub {{in_array(Request::path(), ['ticket/ticketList', 'ticket/replyTicket', 'admin/articleList', 'admin/addArticle', 'admin/editArticle', 'marketing/pushList', 'marketing/emailList']) ? 'active open' : ''}}">
                <a href="javascript:void(0)">
                    <i class="site-menu-icon wb-chat-working" aria-hidden="true"></i>
                    <span class="site-menu-title">客服系统</span>
                </a>
                <ul class="site-menu-sub">
                    <li class="site-menu-item {{in_array(Request::path(), ['ticket/ticketList', 'ticket/replyTicket']) ? 'active open' : ''}}">
                        <a href="/ticket/ticketList" class="animsition-link">
                            <span class="site-menu-title">服务工单</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/articleList', 'admin/addArticle', 'admin/editArticle']) ? 'active open' : ''}}">
                        <a href="/admin/articleList" class="animsition-link">
                            <span class="site-menu-title">文章管理</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['marketing/pushList']) ? 'active open' : ''}}">
                        <a href="/marketing/pushList" class="animsition-link">
                            <span class="site-menu-title">消息推送</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['marketing/emailList']) ? 'active open' : ''}}">
                        <a href="/marketing/emailList" class="animsition-link">
                            <span class="site-menu-title">邮件群发</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="site-menu-item has-sub {{in_array(Request::path(), ['admin/nodeList', 'admin/addNode', 'admin/editNode', 'admin/nodeMonitor', 'admin/labelList', 'admin/addLabel', 'admin/editLabel', 'admin/groupList', 'admin/addGroup', 'admin/editGroup']) ? 'active open' : ''}}">
                <a href="javascript:void(0)">
                    <i class="site-menu-icon wb-grid-4" aria-hidden="true"></i>
                    <span class="site-menu-title">线路系统</span>
                </a>
                <ul class="site-menu-sub">
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/nodeList', 'admin/addNode', 'admin/editNode', 'admin/nodeMonitor']) ? 'active open' : ''}}">
                        <a href="/admin/nodeList" class="animsition-link">
                            <span class="site-menu-title">线路管理</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/labelList', 'admin/addLabel', 'admin/editLabel']) ? 'active open' : ''}}">
                        <a href="/admin/labelList">
                            <span class="site-menu-title">标签管理</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/groupList', 'admin/addGroup', 'admin/editGroup']) ? 'active open' : ''}}">
                        <a href="/admin/groupList" class="animsition-link">
                            <span class="site-menu-title">分组管理</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="site-menu-item has-sub {{in_array(Request::path(), ['shop/goodsList', 'shop/addGoods', 'shop/editGoods', 'coupon/couponList', 'coupon/addCoupon']) ? 'active open' : ''}}">
                <a href="javascript:void(0)">
                    <i class="site-menu-icon wb-shopping-cart" aria-hidden="true"></i>
                    <span class="site-menu-title">商品系统</span>
                </a>
                <ul class="site-menu-sub">
                    <li class="site-menu-item {{in_array(Request::path(), ['shop/goodsList', 'shop/addGoods', 'shop/editGoods']) ? 'active open' : ''}}">
                        <a href="/shop/goodsList" class="animsition-link">
                            <span class="site-menu-title">商品管理</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['coupon/couponList', 'coupon/addCoupon']) ? 'active open' : ''}}">
                        <a href="/coupon/couponList" class="animsition-link">
                            <span class="site-menu-title">卡券管理</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="site-menu-item has-sub {{in_array(Request::path(), ['admin/inviteList', 'admin/applyList', 'admin/applyDetail', 'admin/userRebateList']) ? 'active open' : ''}}">
                <a href="javascript:void(0)">
                    <i class="site-menu-icon wb-thumb-up" aria-hidden="true"></i>
                    <span class="site-menu-title">推广系统</span>
                </a>
                <ul class="site-menu-sub">
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/inviteList']) ? 'active open' : ''}}">
                        <a href="/admin/inviteList" class="animsition-link">
                            <span class="site-menu-title">邀请管理</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/applyList', 'admin/applyDetail']) ? 'active open' : ''}}">
                        <a href="/admin/applyList" class="animsition-link">
                            <span class="site-menu-title">提现管理</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/userRebateList']) ? 'active open' : ''}}">
                        <a href="/admin/userRebateList" class="animsition-link">
                            <span class="site-menu-title">返利流水</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="site-menu-item has-sub {{in_array(Request::path(), ['admin/orderList', 'admin/trafficLog', 'admin/emailLog', 'payment/callbackList', 'logs']) ? 'active open' : ''}}">
                <a href="javascript:void(0)">
                    <i class="site-menu-icon wb-calendar" aria-hidden="true"></i>
                    <span class="site-menu-title">日志系统</span>
                </a>
                <ul class="site-menu-sub">
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/orderList']) ? 'active open' : ''}}">
                        <a href="/admin/orderList" class="animsition-link">
                            <span class="site-menu-title">商品订单</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/trafficLog']) ? 'active open' : ''}}">
                        <a href="/admin/trafficLog" class="animsition-link">
                            <span class="site-menu-title">流量使用</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/emailLog']) ? 'active open' : ''}}">
                        <a href="/admin/emailLog" class="animsition-link">
                            <span class="site-menu-title">邮件投递</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['payment/callbackList']) ? 'active open' : ''}}">
                        <a href="/payment/callbackList" class="animsition-link">
                            <span class="site-menu-title">支付回调</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['logs']) ? 'active open' : ''}}">
                        <a href="/logs" class="animsition-link">
                            <span class="site-menu-title">系统运行</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="site-menu-item has-sub {{in_array(Request::path(), ['admin/decompile', 'admin/convert', 'admin/import', 'admin/analysis', 'sensitiveWords/list', 'sensitiveWords/add']) ? 'active open' : ''}}">
                <a href="javascript:void(0)">
                    <i class="site-menu-icon wb-briefcase" aria-hidden="true"></i>
                    <span class="site-menu-title">工具箱</span>
                </a>
                <ul class="site-menu-sub">
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/decompile']) ? 'active open' : ''}}">
                        <a href="/admin/decompile" class="animsition-link">
                            <span class="site-menu-title">反解析</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/convert']) ? 'active open' : ''}}">
                        <a href="/admin/convert" class="animsition-link">
                            <span class="site-menu-title">格式转换</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/import']) ? 'active open' : ''}}">
                        <a href="/admin/import" class="animsition-link">
                            <span class="site-menu-title">数据导入</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/analysis']) ? 'active open' : ''}}">
                        <a href="/admin/analysis" class="animsition-link">
                            <span class="site-menu-title">日志分析</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['sensitiveWords/list', 'sensitiveWords/add']) ? 'active open' : ''}}">
                        <a href="/sensitiveWords/list" class="animsition-link">
                            <span class="site-menu-title">敏感词管理</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="site-menu-item has-sub {{in_array(Request::path(), ['admin/config', 'admin/system']) ? 'active open' : ''}}">
                <a href="javascript:void(0)">
                    <i class="site-menu-icon wb-settings" aria-hidden="true"></i>
                    <span class="site-menu-title">设置</span>
                </a>
                <ul class="site-menu-sub">
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/config']) ? 'active open' : ''}}">
                        <a href="/admin/config" class="animsition-link">
                            <span class="site-menu-title">通用配置</span>
                        </a>
                    </li>
                    <li class="site-menu-item {{in_array(Request::path(), ['admin/system']) ? 'active open' : ''}}">
                        <a href="/admin/system" class="animsition-link">
                            <span class="site-menu-title">系统设置</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</div>
<div class="page">
    <!--[if lt IE 8]> <p class="browserupgrade">您正在使用 <strong>过时/老旧</strong> 的浏览器。 为了您的使用体验，请
        <a href="http://browsehappy.com/">升级您的浏览器</a> </br>You are using an <strong>outdated</strong> browser. Please
        <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
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
<script src="/assets/global/vendor/intro-js/intro.min.js" type="text/javascript"></script>
<script src="/assets/global/vendor/screenfull/screenfull.js" type="text/javascript"></script>
<script src="/assets/global/vendor/slidepanel/jquery-slidePanel.min.js" type="text/javascript"></script>
<!--[if lt IE 11]>
<script src="/assets/custom/Plugin/sweetalert2/polyfill.min.js"></script>
<![endif]-->
<script src="/assets/custom/Plugin/sweetalert2/sweetalert2.min.js"></script>

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
<script>
    Config.set('assets', '/assets');
</script>
<!-- 页面/Page -->
<script src="/assets/js/Site.js" type="text/javascript"></script>
<script src="/assets/global/js/Plugin/asscrollable.js" type="text/javascript"></script>
<script src="/assets/global/js/Plugin/slidepanel.js" type="text/javascript"></script>
<script src="/assets/custom/Plugin/js-cookie/js.cookie.min.js" type="text/javascript"></script>
<script>
    (function (document, window, $) {
        'use strict';
        var Site = window.Site;
        $(document).ready(function () {
            Site.run();
        });
    })(document, window, jQuery);
</script>
@yield('script')
</body>
</html>
