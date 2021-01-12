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

        <!-- start header -->
        <header class="page-header {{ (Route::currentRouteName() !== 'home' && Request::segment(1) !== 'home' && Request::segment(1) !== 'feature') ? ' page-header--light' : '' }}">
            @if (Route::currentRouteName() === "home" || Request::segment(1) === "home" || Request::segment(1) === "feature")
                <a href="{{ url('/') }}" class="logo"><img src="{{ asset('assets/static/mobile/images/logos/ritavpn-logo.png') }}" alt="77 VPN"></a>
            @else
                <a href="{{ url('/') }}" class="logo"><img src="{{ asset('assets/static/mobile/images/logos/ritavpn-logo-v2.png') }}" alt="77 VPN"></a>
            @endif
            <div class="page-header__right">
                <button class="menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </header>
        <!-- ./end header -->

        <div class="m-menu">
            <div class="m-menu__head">
                <a class="logo" href="#"><img src="{{ asset('assets/static/mobile/images/logos/ritavpn-logo.png') }}" alt="ritavpn"></a>
                <button class="m-menu__close js-close-menu"><i class="fas fa-times"></i></button>
            </div>

            <nav class="m-menu__nav">
                <ul>
                    <li><a href="{{ route('home', app()->getLocale()) }}">{{ __('static.homepage') }}</a></li>
                    <li><a href="{{ route('feature', app()->getLocale()) }}">{{ __('static.features') }}</a></li>
                    <li><a href="{{ route('price', app()->getLocale()) }}">{{ __('static.price') }}</a></li>
                    <li><a href="{{ route('vpn-apps', app()->getLocale()) }}">{{ __('static.download') }}</a></li>
                    <li><a href="{{ route('contact', app()->getLocale()) }}">{{ __('static.contact') }}</a></li>
                </ul>
            </nav>

            @if (!Auth::check())
            <div class="m-menu__btn-group">
                <a href="{{url('account-n')}}" class="cs-btn cs-btn--primary">{{ __('static.mbl_login') }}</a>
                <a href="{{url('account-n')}}" class="cs-btn cs-btn--outline">{{ __('static.mbl_register') }}</a>
            </div>
            @endif

            @if (Auth::check())
            <div class="m-menu__auth-info">
                <div class="auth-email">{{Auth::user()->username}}</div>
                    <ul class="auth-list m-menu__btn-group">
                        <li><a class="cs-btn cs-btn--outline" href="{{url('usercenter')}}">Account</a></li>
                        <li><a class="cs-btn cs-btn--outline" href="{{url('logout')}}">Logout</a></li>
                    </ul>
             </div>
            @endif

            <div class="select-wrapper">
                <select class="js-footer-lang">
                    <option data-value="en" value="en" {{  app()->getLocale() === 'en' ? 'selected' : '' }}>English</option>
                    <option data-value="ch" value="ch" {{  app()->getLocale() === 'zh-CN' ? 'selected' : '' }}>Chinese</option>
                </select>
            </div>
        </div>
