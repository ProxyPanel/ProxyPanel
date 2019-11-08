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
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="兔姬菌">
    <meta name="copyright" content="2017-2019©兔姬菌">
    <title>@yield('title')</title>
    <link rel="shortcut icon" href="{{asset('favicon.ico')}}">
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/global/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/global/css/bootstrap-extend.min.css">
    <link rel="stylesheet" href="/assets/css/site.min.css">
    <!-- Plugins -->
    <link rel="stylesheet" href="/assets/global/vendor/animsition/animsition.min.css">
    <link rel="stylesheet" href="/assets/global/vendor/asscrollable/asScrollable.min.css">
    <link rel="stylesheet" href="/assets/global/vendor/intro-js/introjs.min.css">
    <link rel="stylesheet" href="/assets/global/vendor/slidepanel/slidePanel.min.css">
    <link rel="stylesheet" href="/assets/global/vendor/flag-icon-css/flag-icon.min.css">
    <link rel="stylesheet" href="/assets/examples/css/pages/login-v3.css">
@yield('css')
<!-- Fonts -->
    <link rel="stylesheet" href="/assets/global/fonts/web-icons/web-icons.min.css">
    <link rel="stylesheet" href="/assets/global/fonts/brand-icons/brand-icons.min.css">
    <link rel='stylesheet' href='https://fonts.loli.net/css?family=Roboto:300,400,500,300italic'>
    <!--[if lt IE 9]>
    <script src="/assets/global/vendor/html5shiv/html5shiv.min.js"></script> <![endif]-->
    <!--[if lt IE 10]>
    <script src="/assets/global/vendor/media-match/media.match.min.js"></script>
    <script src="/assets/global/vendor/respond/respond.min.js"></script> <![endif]-->
    <!-- Scripts -->
    <script src="/assets/global/vendor/breakpoints/breakpoints.min.js"></script>
    <script>
        Breakpoints();
    </script>
</head>
<body class="animsition page-login-v3 layout-full" style="position: relative;">
<!--[if lt IE 8]> <p class="browserupgrade">您正在使用 <strong>过时/老旧</strong> 的浏览器。 为了您的使用体验，请
    <a href="http://browsehappy.com/">升级您的浏览器</a> </br>You are using an <strong>outdated</strong> browser. Please
    <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->
@if(Agent::isMobile() && Agent::isiOS() && strpos(Agent::getUserAgent(), 'MicroMessenger') !== false)
    <style>
        .cover-up {
            opacity: 0.1;
            filter: alpha(opacity=10);
        }
    </style>
    <div  class="m-0 p-0 w-full h-full text-white" style="z-index: 10; position: absolute;">
        <div class="font-size-16 h-p33 pl-20 pt-20" style="line-height: 1.8; background: url(//gw.alicdn.com/tfs/TB1eSZaNFXXXXb.XXXXXXXXXXXX-750-234.png) center top/contain no-repeat">
            <p>点击右上角 <i class="icon wb-more-horizontal"></i>，选择在<img src="//gw.alicdn.com/tfs/TB1xwiUNpXXXXaIXXXXXXXXXXXX-55-55.png" class="w-30 h-30 vertical-align-middle m-3" alt="Safari"/> Safari 中打开
            </p>
            <p>您就可以正常访问本站了呦~</p>
        </div>
    </div>
@endif
<div class="page vertical-align text-center cover-up" data-animsition-in="fade-in" data-animsition-out="fade-out">
    <div class="page-content vertical-align-middle animation-slide-top animation-duration-1">
        <div class="panel">
            <div class="panel-heading">
                <div class="panel-title">
                    <div class="brand">
                        @if(\App\Components\Helpers::systemConfig()['website_logo'])
                            <img class="brand-img" src="{{\App\Components\Helpers::systemConfig()['website_logo']}}" width="70px" alt="Logo">
                        @else
                            <img class="brand-img" src="/assets/images/logo64.png" alt="Logo">
                        @endif
                        <h3 class="brand-text">{{\App\Components\Helpers::systemConfig()['website_name']}}</h3>
                    </div>
                </div>
                <div class="ribbon ribbon-reverse ribbon-info ribbon-clip">
                    <button class="ribbon-inner btn dropdown-toggle pt-0" id="language" data-toggle="dropdown" aria-expanded="false">
                        <i class="font-size-20 wb-globe"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-bullet" aria-labelledby="language" role="menu">
                        <a class="dropdown-item" href="{{url('lang', ['locale' => 'zh-CN'])}}" role="menuitem">
                            <span class="flag-icon flag-icon-cn"></span>
                            简体中文</a>
                        <a class="dropdown-item" href="{{url('lang', ['locale' => 'zh-tw'])}}" role="menuitem">
                            <span class="flag-icon flag-icon-tw"></span>
                            繁體中文</a>
                        <a class="dropdown-item" href="{{url('lang', ['locale' => 'en'])}}" role="menuitem">
                            <span class="flag-icon flag-icon-gb"></span>
                            English</a>
                        <a class="dropdown-item" href="{{url('lang', ['locale' => 'ja'])}}" role="menuitem">
                            <span class="flag-icon flag-icon-jp"></span>
                            日本語</a>
                        <a class="dropdown-item" href="{{url('lang', ['locale' => 'ko'])}}" role="menuitem">
                            <span class="flag-icon flag-icon-kr"></span>
                            한국어</a>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                @yield('content')
            </div>
        </div>
    </div>
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
<script src="/assets/global/vendor/jquery-placeholder/jquery.placeholder.js"></script>
<!-- 脚本/Scripts -->
<script src="/assets/global/js/Component.js"></script>
<script src="/assets/global/js/Plugin.js"></script>
<script src="/assets/global/js/Base.js"></script>
<script src="/assets/global/js/Config.js"></script>
<script src="/assets/js/Section/Menubar.js"></script>
<script src="/assets/js/Section/Sidebar.js"></script>
<script src="/assets/js/Section/PageAside.js"></script>
<script src="/assets/js/Plugin/menu.js"></script>
<!-- 设置/Config -->
<script src="/assets/global/js/config/colors.js"></script>
<script>
    Config.set('assets', '/assets');
</script>
<!-- Page -->
@yield('script')
<script src="/assets/js/Site.js" type="text/javascript"></script>
<script src="/assets/global/js/Plugin/asscrollable.js" type="text/javascript"></script>
<script src="/assets/global/js/Plugin/slidepanel.js" type="text/javascript"></script>
<script src="/assets/global/js/Plugin/switchery.js" type="text/javascript"></script>
<script src="/assets/global/js/Plugin/jquery-placeholder.js" type="text/javascript"></script>
<script src="/assets/global/js/Plugin/material.js" type="text/javascript"></script>
<!-- 统计 -->
{!! \App\Components\Helpers::systemConfig()['website_analytics'] !!}
<!-- 客服 -->
{!! \App\Components\Helpers::systemConfig()['website_customer_service'] !!}
<script>
    (function (document, window, $) {
        'use strict';
        var Site = window.Site;
        $(document).ready(function () {
            Site.run();
        });
    })(document, window, jQuery);

    // 登录
    function login() {
        window.location.href = '/login';
    }
</script>
</body>
</html>