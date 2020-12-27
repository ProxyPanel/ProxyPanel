<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=11">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-param" content="_csrf">
    <meta name="csrf-token" content="">
    <title>{{sysConfig('website_name')}}</title>
    <meta name="keywords" content="">
    @yield('header-script')
    <link rel="shortcut icon" href="build//rita.ico">
</head>

<body>

    <!-- start wrapper -->
    <div class="page-wrapper">
            <!-- start header: V1 version like home, features, help -->

            <header class="page-header">
                @if (Request::segment(1) === "vpn-apps" || Request::segment(1) === "price" || Request::segment(1) === "contact")
                <a href="{{ route('home', app()->getLocale()) }}" class="logo"><img src="{{ asset('assets/static/desktop/images/logos/ritavpn-logo-v2.png') }}" alt="77 VPN"></a>
                @else
                    <a href="{{ route('home', app()->getLocale()) }}" class="logo"><img src="{{ asset('assets/static/desktop/images/logos/ritavpn-logo.png') }}" alt="77 VPN"></a>
                @endif
                <div class="page-header__right">
                    <nav class="page-header__nav {{ (Request::segment(1) === 'vpn-apps' || Request::segment(1) === 'price' || Request::segment(1) === 'contact') ? ' page-header__nav--dark' : '' }}">
                        <ul>
                            <li><a href="{{ route('feature', app()->getLocale()) }}">{{ __('static.features') }}</a></li>
                            <li><a href="{{ route('price', app()->getLocale()) }}">{{ __('static.price') }}</a></li>
                            <li><a href="{{ route('vpn-apps', app()->getLocale()) }}">{{ __('static.download') }}</a></li>
                            <li><a href="{{ route('help-n', app()->getLocale()) }}">{{ __('static.help') }}</a></li>
                        </ul>
                    </nav>
                    @if (!Auth::check())
                    <div class="page-header__actions {{ (Request::segment(1) === 'vpn-apps' || Request::segment(1) === 'price') || Request::segment(1) === 'contact' ? ' page-header__actions--dark' : '' }}">
                        <span class="icon" aria-hidden="true"><i class="fas fa-user"></i></span>
                        <span class="action-link" data-toggle="modal" data-target="#signinModal">{{ __('static.login') }}</span>
                        <span class="action-link" data-toggle="modal" data-target="#signupModal">{{ __('static.register') }}</span>
                    </div>
                    @endif
                    @if (Auth::check())
                    <div class="page-header__auth-info {{ (Request::segment(1) === 'vpn-apps' || Request::segment(1) === 'price' || Request::segment(1) === 'contact') ? ' page-header__auth-info--dark' : '' }}">
                        <ul class="auth-list">
                            <li><a href="{{url('usercenter')}}"> {{Auth::user()->username}}</a></li>
                            <li><a href="{{url('logout')}}">Logout</a></li>
                        </ul>
                    </div>
                    @endif
                    <div class="page-header__lang {{ (Request::segment(1) === 'vpn-apps' || Request::segment(1) === 'price' || Request::segment(1) === 'contact') ? ' page-header__lang--dark' : '' }}">
                        <a href="{{ url('lang/en') }}" class="{{  app()->getLocale() === 'en' ? 'is-active' : '' }}">English</a>
                        <a href="{{ url('lang/zh-CN') }}" class="{{  app()->getLocale() === 'zh-CN' ? 'is-active' : '' }}">Chinese</a>
                    </div>
                </div>
            </header>
            <!-- ./end header -->


            <!-- FOR HELP SUBPAGE -->
            @if (Request::segment(2) == 'subpage')<!-- DON'T USED -->
                <!-- <header class="page-header--help">
                    <a href="index.html" class="logo">RitaVPN</a>
                    <div class="page-header--help__navbar">
                        <nav>
                            <ul>
                                <li><a href="#">Home</a></li>
                                <li><a href="#">Price</a></li>
                                <li><a href="#">VPN Downloads</a></li>
                                <li><a href="#">Internet Security and Privacy</a></li>
                                <li><a href="#">How To</a></li>
                                <li><a href="#">News</a></li>
                                <li><a href="#">Hello, I am RitaVPN</a></li>
                            </ul>
                        </nav>
                    </div>
                </header> -->
            @endif

