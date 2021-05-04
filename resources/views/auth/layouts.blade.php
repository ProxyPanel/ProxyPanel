@extends('_layout')
@section('title', sysConfig('website_name'))
@section('layout_css')
    <link href="/assets/css/login-v3.min.css" rel="stylesheet">
    @yield('css')
@endsection
@section('body_class', 'page-login-v3 layout-full position-relative')
@section('layout_content')
    @if(Agent::isMobile() && Agent::is('iOS') && strpos(Agent::getUserAgent(), 'MicroMessenger') !== false)
        <style type="text/css">
            .cover-up {
                opacity: 0.1;
                filter: alpha(opacity=10);
            }
        </style>
        <div class="m-0 p-0 w-full h-full text-white" style="z-index: 10; position: absolute;">
            <div class="font-size-16 h-p33 pl-20 pt-20"
                 style="line-height: 1.8; background: url(//gw.alicdn.com/tfs/TB1eSZaNFXXXXb.XXXXXXXXXXXX-750-234.png) center top/contain no-repeat">
                <p>{{trans('common.to_safari.0')}} <i class="icon wb-more-horizontal"></i>{{trans('common.to_safari.1')}}
                    <img src="//gw.alicdn.com/tfs/TB1xwiUNpXXXXaIXXXXXXXXXXXX-55-55.png" class="w-30 h-30 vertical-align-middle m-3" alt="Safari"/>
                    {{trans('common.to_safari.2')}}<br>{{trans('common.to_safari.3')}}</p>
            </div>
        </div>
    @endif
    <div class="page vertical-align text-center cover-up" data-animsition-in="fade-in" data-animsition-out="fade-out">
        <div class="page-content vertical-align-middle">
            <div class="animation-slide-top animation-duration-1">
                <div class="panel">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <div class="brand">
                                <img src="{{sysConfig('website_home_logo')? asset(sysConfig('website_home_logo')) :'/assets/images/logo64.png'}}" class="brand-img" alt="logo"/>
                                <h3 class="brand-text">{{sysConfig('website_name')}}</h3>
                            </div>
                        </div>
                        <div class="ribbon ribbon-reverse ribbon-info ribbon-clip">
                            <button class="ribbon-inner btn dropdown-toggle pt-0" id="language" data-toggle="dropdown" aria-expanded="false">
                                <i class="font-size-20 wb-globe"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-bullet" aria-labelledby="language" role="menu">
                                <a class="dropdown-item" href="{{route('lang', ['locale' => 'en'])}}" role="menuitem">
                                    <i class="flag-icon flag-icon-gb"></i>
                                    <span style="padding: inherit;">English</span></a>
                                <a class="dropdown-item" href="{{route('lang', ['locale' => 'zh_CN'])}}" role="menuitem">
                                    <i class="flag-icon flag-icon-cn"></i>
                                    <span style="padding: inherit;">简体中文</span></a>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                    <!--[if lt IE 8]><p class="browserupgrade">{{trans('common.update_browser.0')}}<strong>{{trans('common.update_browser.1')}}</strong>
{{trans('common.update_browser.2')}}<a href="http://browsehappy.com/" target="_blank">{{trans('common.update_browser.3')}}</a>{{trans('common.update_browser.4')}}</p><![endif]-->
                        @yield('content')
                    </div>
                </div>
            </div>
            @yield('modal')
        </div>
    </div>
@endsection
@section('layout_javascript')
    <script src="/assets/global/vendor/jquery-placeholder/jquery.placeholder.min.js"></script>
    <script src="/assets/global/js/Plugin/jquery-placeholder.js"></script>
    <script src="/assets/global/js/Plugin/material.js"></script>
    @yield('javascript')
    <!-- 统计 -->
    {!! sysConfig('website_analytics') !!}
    <!-- 客服 -->
    {!! sysConfig('website_customer_service') !!}
@endsection
