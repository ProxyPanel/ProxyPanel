<!DOCTYPE html>
<!--[if IE 8]> <html lang="{{app()->getLocale()}}" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="{{app()->getLocale()}}" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="{{app()->getLocale()}}">
<!--<![endif]-->

<head>
    <meta charset="utf-8" />
    <title>@yield('title')</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <meta content="" name="description" />
    <meta content="" name="author" />
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN THEME GLOBAL STYLES -->
    <link href="/assets/global/css/components-rounded.min.css" rel="stylesheet" id="style_components" type="text/css" />
    <!-- END THEME GLOBAL STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    @yield('css')
    <!-- END PAGE LEVEL STYLES -->
    <!-- BEGIN THEME LAYOUT STYLES -->
    <!-- END THEME LAYOUT STYLES -->
    <link rel="shortcut icon" href="{{asset('favicon.ico')}}" />
</head>

<body class="login">
    <!-- BEGIN LOGO -->
    <div class="logo">
        @if(\App\Components\Helpers::systemConfig()['website_home_logo'])
            <a href="{{url('/')}}"> <img src="{{\App\Components\Helpers::systemConfig()['website_home_logo']}}" alt="" style="max-width:300px; max-height:90px;"/> </a>
        @else
            <a href="{{url('/')}}"> <img src="/assets/images/home_logo.png" alt="" /> </a>
        @endif
    </div>
    <!-- END LOGO -->
    <!-- BEGIN LOGIN -->
    <div class="content">
        <nav style="padding-bottom: 20px;text-align: center;">
            @if(app()->getLocale() == 'zh-CN')
                <a href="{{url('lang', ['locale' => 'zh-tw'])}}">繁體中文</a>
                <a href="{{url('lang', ['locale' => 'en'])}}">English</a>
                <a href="{{url('lang', ['locale' => 'ja'])}}">日本語</a>
                <a href="{{url('lang', ['locale' => 'ko'])}}">한국어</a>
            @elseif(app()->getLocale() == 'zh-tw')
                <a href="{{url('lang', ['locale' => 'zh-CN'])}}">简体中文</a>
                <a href="{{url('lang', ['locale' => 'en'])}}">English</a>
                <a href="{{url('lang', ['locale' => 'ja'])}}">日本語</a>
                <a href="{{url('lang', ['locale' => 'ko'])}}">한국어</a>
            @elseif(app()->getLocale() == 'en')
                <a href="{{url('lang', ['locale' => 'zh-CN'])}}">简体中文</a>
                <a href="{{url('lang', ['locale' => 'zh-tw'])}}">繁體中文</a>
                <a href="{{url('lang', ['locale' => 'ja'])}}">日本語</a>
                <a href="{{url('lang', ['locale' => 'ko'])}}">한국어</a>
            @elseif(app()->getLocale() == 'ko')
                <a href="{{url('lang', ['locale' => 'zh-CN'])}}">简体中文</a>
                <a href="{{url('lang', ['locale' => 'zh-tw'])}}">繁體中文</a>
                <a href="{{url('lang', ['locale' => 'en'])}}">English</a>
                <a href="{{url('lang', ['locale' => 'ja'])}}">日本語</a>
            @elseif(app()->getLocale() == 'ja')
                <a href="{{url('lang', ['locale' => 'zh-CN'])}}">简体中文</a>
                <a href="{{url('lang', ['locale' => 'zh-tw'])}}">繁體中文</a>
                <a href="{{url('lang', ['locale' => 'en'])}}">English</a>
                <a href="{{url('lang', ['locale' => 'ko'])}}">한국어</a>
            @else
            @endif
        </nav>
        @yield('content')
    </div>

    <!-- END LOGIN -->
    <!--[if lt IE 9]>
    <script src="/assets/global/plugins/respond.min.js"></script>
    <script src="/assets/global/plugins/excanvas.min.js"></script>
    <script src="/assets/global/plugins/ie8.fix.min.js"></script>
    <![endif]-->
    <script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    @yield('script')

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-122312249-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-122312249-1');
    </script>

    <!-- 统计 -->
    {!! \App\Components\Helpers::systemConfig()['website_analytics'] !!}
    <!-- 客服 -->
    {!! \App\Components\Helpers::systemConfig()['website_customer_service'] !!}
</body>

</html>