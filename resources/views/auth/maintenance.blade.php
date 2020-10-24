<!DOCTYPE html>
<!--[if IE 8]>
<html lang="{{app()->getLocale()}}" class="ie8 no-js css-menubar"> <![endif]-->
<!--[if IE 9]>
<html lang="{{app()->getLocale()}}" class="ie9 no-js css-menubar"> <![endif]-->
<!--[if !IE]><!-->
<html lang="{{app()->getLocale()}}">
<!--<![endif]-->
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="兔姬菌">
    <meta name="copyright" content="2017-2020©兔姬菌">
    <title>维护 | Maintenance</title>
    <link href="{{asset('favicon.ico')}}" rel="shortcut icon apple-touch-icon">
    <!-- Stylesheets -->
    <link href="/assets/global/css/bootstrap.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/css/bootstrap-extend.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/css/site.min.css" type="text/css" rel="stylesheet">
    <!-- Plugins -->
    <link href="/assets/global/vendor/animsition/animsition.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/vendor/asscrollable/asScrollable.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/vendor/slidepanel/slidePanel.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/vendor/flag-icon-css/flag-icon.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/examples/css/pages/maintenance.css" type="text/css" rel="stylesheet">
    <!-- Fonts -->
    <link href="/assets/global/fonts/web-icons/web-icons.min.css" type="text/css" rel="stylesheet">
    <link href="//fonts.loli.net/css?family=Roboto:300,400,500,300italic" type="text/css" rel="stylesheet">

    <!--[if lt IE 9]>
    <script src="/assets/global/vendor/html5shiv/html5shiv.min.js" type="text/javascript"></script> <![endif]-->
    <!--[if lt IE 10]>
    <script src="/assets/global/vendor/media-match/media.match.min.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/respond/respond.min.js" type="text/javascript"></script> <![endif]-->

    <!-- Scripts -->
    <script src="/assets/global/vendor/breakpoints/breakpoints.js" type="text/javascript"></script>
    <script>
        Breakpoints();
    </script>
</head>
<body class="animsition page-login-v3 layout-full" style="position: relative;">
<!--[if lt IE 8]> <p class="browserupgrade">您正在使用 <strong>过时/老旧</strong> 的浏览器。 为了您的使用体验，请
    <a href="http://browsehappy.com/" target="_blank">升级您的浏览器</a> <br/>You are using an <strong>outdated</strong>
    browser. Please
    <a href="http://browsehappy.com/" target="_blank">upgrade your browser</a> to improve your experience.</p>
<![endif]-->

<!-- Page -->
<div class="page vertical-align text-center" data-animsition-in="fade-in" data-animsition-out="fade-out">>
    <div class="page-content vertical-align-middle">
        <i class="icon wb-settings icon-spin page-maintenance-icon" aria-hidden="true"></i>
        <h2>维护建设中</h2>
        {!! $message !!}
        <footer class="page-copyright">
            <p id="countdown"></p>
        </footer>
    </div>
</div>
<!-- End Page -->

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
<script src="/assets/global/vendor/screenfull/screenfull.js"></script>
<script src="/assets/global/vendor/slidepanel/jquery-slidePanel.js"></script>
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
<!-- Page -->
<script src="/assets/js/Site.js"></script>
<script src="/assets/global/js/Plugin/asscrollable.js"></script>
<script src="/assets/global/js/Plugin/slidepanel.js"></script>

<script>
    (function (document, window, $) {
        'use strict';

        var Site = window.Site;
        $(document).ready(function () {
            Site.run();
        });
    })(document, window, jQuery);

    // 每秒更新计时器
    const countDownDate = new Date("{{$time}}").getTime();
    const x = setInterval(function () {
        const distance = countDownDate - new Date().getTime();
        const days = Math.floor(distance / 86400000);
        const hours = Math.floor(distance % 86400000 / 3600000);
        const minutes = Math.floor(distance % 3600000 / 60000);
        const seconds = Math.floor(distance % 60000 / 1000);
        document.getElementById('countdown').innerHTML = '<h2>' + days + ' <span> 天 </span>: ' + hours +
            '    <span>时</span>: ' + minutes + ' <span>分 </span>: ' + seconds + '<span> 秒</span> </h2>';
        if (distance <= 0) {
            clearInterval(x);
            document.getElementById('countdown').remove();
        }
    }, 1000);
</script>
</body>
</html>
