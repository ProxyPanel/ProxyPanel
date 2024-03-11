<!DOCTYPE html>
<!--[if IE 8]>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="ie8 no-js css-menubar"> <![endif]-->
<!--[if IE 9]>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="ie9 no-js css-menubar"> <![endif]-->
<!--[if !IE]><!-->
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
          content="An account management Panel based on Laravel7 framework. Include multiple payment, account management, system caching, admin notification, products models, and more.">
    <meta name="keywords" content="ProxyPanel Laravel Shadowsocks ShadowsocksR V2Ray Trojan VNET VPN">
    <meta name="author" content="ZBrettonYe">
    <meta name="copyright" content="2017-2024©ProxyPanel">
    <title>@yield('title')</title>
    <link href="{{asset('favicon.ico')}}" rel="shortcut icon apple-touch-icon">
    <!-- 样式表/Stylesheets -->
    <link href="/assets/bundle/app.min.css" rel="stylesheet">
    <link rel="stylesheet" href="//cdn.staticfile.org/flag-icons/6.6.6/css/flag-icons.min.css"/>
    @yield('layout_css')
    <!-- 字体/Fonts -->
    <link href="/assets/global/fonts/web-icons/web-icons.min.css" rel="stylesheet">
    <link href="/assets/css/font.css" rel="stylesheet">
    <!--[if lt IE 9]>
    <script src="//cdn.staticfile.org/html5shiv/3.7.3/html5shiv.min.js"></script>
    <![endif]-->
    <!--[if lt IE 10]>
    <script src="/assets/global/vendor/media-match/media.match.min.js"></script>
    <script src="/assets/global/vendor/respond/respond.min.js"></script>
    <![endif]-->
    <!-- Scripts -->
    <script src="/assets/global/vendor/breakpoints/breakpoints.min.js"></script>
    <script>
      Breakpoints();
    </script>
    @if (config('theme.skin'))
        <link id="skinStyle" href="/assets/css/skins/{{config('theme.skin')}}.min.css" rel="stylesheet" type="text/css">
    @endif
</head>

<body class="animsition @yield('body_class')">
@yield('layout_content')
<!-- 核心/Core -->
<script src="/assets/global/vendor/babel-external-helpers/babel-external-helpers.js"></script>
<script src="/assets/global/vendor/jquery/jquery.min.js"></script>
<script src="/assets/global/vendor/popper-js/umd/popper.min.js"></script>
<script src="/assets/global/vendor/bootstrap/bootstrap.min.js"></script>
<script src="/assets/global/vendor/animsition/animsition.min.js"></script>
<script src="/assets/global/vendor/mousewheel/jquery.mousewheel.min.js"></script>
<script src="/assets/global/vendor/asscrollbar/jquery-asScrollbar.min.js"></script>
<script src="/assets/global/vendor/asscrollable/jquery-asScrollable.min.js"></script>
<script src="/assets/global/vendor/ashoverscroll/jquery-asHoverScroll.min.js"></script>
<!-- 插件/Plugins -->
<script src="/assets/global/vendor/screenfull/screenfull.min.js"></script>
<script src="/assets/global/vendor/slidepanel/jquery-slidePanel.min.js"></script>
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
<!-- 页面/Page -->
<script src="/assets/js/Site.js"></script>
<script src="/assets/global/js/Plugin/asscrollable.js"></script>
<script src="/assets/global/js/Plugin/slidepanel.js"></script>
<script>
  (function(document, window, $) {
    'use strict';
    const Site = window.Site;
    $(document).ready(function() {
      Site.run();
    });
  })(document, window, jQuery);
</script>
@yield('layout_javascript')
</body>
</html>
