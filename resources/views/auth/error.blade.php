<!DOCTYPE html>
<!--[if IE 8]>
<html lang="{{app()->getLocale()}}" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]>
<html lang="{{app()->getLocale()}}" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="{{app()->getLocale()}}">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="错误">
    <meta name="author" content="兔姬菌">
    <title>{{trans('error.title')}}</title>

    <link href="{{asset('favicon.ico')}}" rel="shortcut icon">
    <!-- 样式表/Stylesheets -->
    <link href="/assets/global/css/bootstrap.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/css/bootstrap-extend.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/css/site.min.css" type="text/css" rel="stylesheet">
    <!-- 插件/Plugins -->
    <link href="/assets/global/vendor/animsition/animsition.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/vendor/asscrollable/asScrollable.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/vendor/intro-js/introjs.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/vendor/slidepanel/slidePanel.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/vendor/flag-icon-css/flag-icon.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/examples/css/pages/errors.min.css" type="text/css" rel="stylesheet">
    <!-- 字体/Fonts -->
    <link href="/assets/global/fonts/web-icons/web-icons.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/fonts/brand-icons/brand-icons.min.css" type="text/css" rel="stylesheet">
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
</head>
<body class="animsition page-error page-error-400 layout-full">
<!--[if lt IE 8]>
<p class="browserupgrade">您正在使用 <strong>过时/老旧</strong> 的浏览器。 为了您的使用体验，请
    <a href="http://browsehappy.com/">升级您的浏览器</a> </br>You are using an <strong>outdated</strong> browser. Please
    <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->

<div class="page vertical-align text-center" data-animsition-in="fade-in" data-animsition-out="fade-out">
    <div class="page-content vertical-align-middle">
        <header>
            <h1 class="animation-slide-top">(。・＿・。)ﾉI’m sorry~</h1>
            <p>{{trans('error.title')}}</p>
        </header>
        <p class="error-advise">{!! $message !!}</p>
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
<script type="text/javascript">
    (function (document, window, $) {
        'use strict';

        const Site = window.Site;
        $(document).ready(function () {
            Site.run();
        });
    })(document, window, jQuery);
</script>
</body>
</html>